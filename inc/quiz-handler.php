<?php
/**
 * SUED Studio — Interactive Quiz Handler
 *
 * Responsibilities:
 *  1. Register a custom DB table `{prefix}sued_quiz_leads` on theme activation & init.
 *  2. Provide a secure REST API endpoint `/wp-json/sued/v1/quiz-submit` with validation,
 *     sanitization, honeypot protection, rate limiting, and LGPD compliance.
 *  3. Dispatch notification emails to the designated team addresses.
 *  4. Provide a modern WP-Admin CRM page styled according to SUED Studio's visual identity.
 *
 * @package SUED_Studio
 */

defined('ABSPATH') || exit;

/* ─── 1. Database — Custom Table `{prefix}sued_quiz_leads` ─────── */

define('SUED_QUIZ_DB_VERSION', '1.0.0');

function sued_create_quiz_table(): void {
    global $wpdb;

    $table   = $wpdb->prefix . 'sued_quiz_leads';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name            VARCHAR(150)        NOT NULL DEFAULT '',
        business_name   VARCHAR(150)        NOT NULL DEFAULT '',
        email           VARCHAR(200)        NOT NULL DEFAULT '',
        phone           VARCHAR(30)         NOT NULL DEFAULT '',
        sector          VARCHAR(50)         NOT NULL DEFAULT '',
        presence        VARCHAR(50)         NOT NULL DEFAULT '',
        traffic         VARCHAR(50)         NOT NULL DEFAULT '',
        goal            VARCHAR(50)         NOT NULL DEFAULT '',
        budget          VARCHAR(50)         NOT NULL DEFAULT '',
        pain            VARCHAR(50)         NOT NULL DEFAULT '',
        score           INT(11)             NOT NULL DEFAULT 0,
        profile         VARCHAR(100)        NOT NULL DEFAULT '',
        answers_json    LONGTEXT            NOT NULL,
        lgpd_consent    TINYINT(1)          NOT NULL DEFAULT 1,
        status          ENUM('new','contacted','converted','disqualified') NOT NULL DEFAULT 'new',
        ip_address      VARCHAR(45)         NOT NULL DEFAULT '',
        user_agent      VARCHAR(500)        NOT NULL DEFAULT '',
        source          VARCHAR(255)        NOT NULL DEFAULT '',
        created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY email (email),
        KEY status (status),
        KEY score (score),
        KEY created_at (created_at)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('sued_quiz_db_version', SUED_QUIZ_DB_VERSION);
}
add_action('after_switch_theme', 'sued_create_quiz_table');

add_action('init', function (): void {
    if (get_option('sued_quiz_db_version') !== SUED_QUIZ_DB_VERSION) {
        sued_create_quiz_table();
    }
});

/* ─── 2. REST API Endpoint: POST /wp-json/sued/v1/quiz-submit ──── */

add_action('rest_api_init', function (): void {
    register_rest_route('sued/v1', '/quiz-submit', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'sued_rest_handle_quiz_submit',
        'permission_callback' => '__return_true', // Nonce check performed inside callback
    ]);
});

/**
 * REST API Callback for Quiz submission.
 */
function sued_rest_handle_quiz_submit(WP_REST_Request $request): WP_REST_Response {
    $params = $request->get_json_params();
    if (empty($params)) {
        $params = $request->get_params();
    }

    // 1. Nonce Verification
    $nonce = $request->get_header('x_wp_nonce') ?: ($params['nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response([
            'success' => false,
            'message' => __('Sessão expirada. Por favor, recarregue a página.', 'sued-studio'),
        ], 403);
    }

    // 2. Honeypot Spam Protection
    if (!empty($params['sued_hp_check']) || !empty($params['website'])) {
        // Silently return success to neutralize bot activity
        return new WP_REST_Response([
            'success' => true,
            'message' => __('Diagnóstico concluído com sucesso.', 'sued-studio'),
        ], 200);
    }

    // 3. IP Rate Limiting (prevent flood from same IP in < 20s)
    $ip = sanitize_text_field(
        wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')
    );
    $ip_clean = preg_replace('/[^0-9a-fA-F:., ]/', '', $ip);
    $transient_key = 'sued_quiz_rate_' . md5($ip_clean);
    if (get_transient($transient_key)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => __('Aguarde alguns instantes antes de enviar novamente.', 'sued-studio'),
        ], 429);
    }
    set_transient($transient_key, 1, 20);

    // 4. Data Extraction & Sanitization
    $name          = sanitize_text_field($params['nome'] ?? $params['name'] ?? '');
    $business_name = sanitize_text_field($params['negocio'] ?? $params['business_name'] ?? '');
    $email         = sanitize_email($params['email'] ?? '');
    $phone         = sanitize_text_field($params['telefone'] ?? $params['phone'] ?? '');
    $sector        = sanitize_text_field($params['setor'] ?? '');
    $presence      = sanitize_text_field($params['presenca'] ?? '');
    $traffic       = sanitize_text_field($params['trafego'] ?? '');
    $goal          = sanitize_text_field($params['objetivo'] ?? '');
    $budget        = sanitize_text_field($params['budget'] ?? '');
    $pain          = sanitize_text_field($params['dor'] ?? '');
    $score         = absint($params['pontuacaoTotal'] ?? $params['score'] ?? 0);
    $profile       = sanitize_text_field($params['perfil'] ?? '');
    $lgpd_consent  = !empty($params['lgpd']) ? 1 : 0;
    $source        = esc_url_raw($params['source'] ?? wp_get_referer() ?: home_url('/quiz'));
    $user_agent    = sanitize_text_field(substr(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500));

    // Raw/Structured answers array to JSON
    $raw_answers = $params['respostas'] ?? [];
    $clean_answers = [];
    if (is_array($raw_answers)) {
        foreach ($raw_answers as $key => $val) {
            $clean_key = sanitize_key($key);
            if (is_array($val)) {
                $clean_answers[$clean_key] = [
                    'valor'  => sanitize_text_field($val['valor'] ?? ''),
                    'label'  => sanitize_text_field($val['label'] ?? ''),
                    'pontos' => absint($val['pontos'] ?? 0),
                ];
            } else {
                $clean_answers[$clean_key] = sanitize_text_field(strval($val));
            }
        }
    }
    $answers_json = wp_json_encode($clean_answers, JSON_UNESCAPED_UNICODE);

    // 5. Validation
    $errors = [];
    if (mb_strlen($name) < 2) {
        $errors[] = __('Por favor, informe seu nome completo.', 'sued-studio');
    }
    if (mb_strlen($business_name) < 2) {
        $errors[] = __('Por favor, informe o nome do seu negócio.', 'sued-studio');
    }
    if (!is_email($email)) {
        $errors[] = __('Por favor, informe um e-mail válido.', 'sued-studio');
    }
    if (!$lgpd_consent) {
        $errors[] = __('O consentimento com os termos da LGPD é obrigatório para prosseguir.', 'sued-studio');
    }

    if (!empty($errors)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => implode(' ', $errors),
        ], 422);
    }

    // 6. Save to Custom DB Table
    global $wpdb;
    $table = $wpdb->prefix . 'sued_quiz_leads';

    $inserted = $wpdb->insert(
        $table,
        [
            'name'          => $name,
            'business_name' => $business_name,
            'email'         => $email,
            'phone'         => $phone,
            'sector'        => $sector,
            'presence'      => $presence,
            'traffic'       => $traffic,
            'goal'          => $goal,
            'budget'        => $budget,
            'pain'          => $pain,
            'score'         => $score,
            'profile'       => $profile,
            'answers_json'  => $answers_json,
            'lgpd_consent'  => $lgpd_consent,
            'status'        => 'new',
            'ip_address'    => $ip_clean,
            'user_agent'    => $user_agent,
            'source'        => $source,
            'created_at'    => current_time('mysql'),
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
    );

    if (false === $inserted) {
        return new WP_REST_Response([
            'success' => false,
            'message' => __('Erro ao processar dados no servidor. Tente novamente.', 'sued-studio'),
        ], 500);
    }

    $lead_id = $wpdb->insert_id;

    // 7. Dispatch Notification Emails
    sued_send_quiz_notification_email($lead_id, [
        'name'          => $name,
        'business_name' => $business_name,
        'email'         => $email,
        'phone'         => $phone,
        'sector'        => $sector,
        'presence'      => $presence,
        'traffic'       => $traffic,
        'goal'          => $goal,
        'budget'        => $budget,
        'pain'          => $pain,
        'score'         => $score,
        'profile'       => $profile,
        'source'        => $source,
        'ip'            => $ip_clean,
        'answers'       => $clean_answers,
    ]);

    return new WP_REST_Response([
        'success' => true,
        'lead_id' => $lead_id,
        'message' => __('Diagnóstico registrado com sucesso!', 'sued-studio'),
    ], 200);
}

/* ─── 3. Email Notification to Team ────────────────────────────── */

/**
 * Dispatches notification emails to the team members defined in new_feature.md.
 */
function sued_send_quiz_notification_email(int $lead_id, array $lead_data): void {
    $recipients = [
        'henrich.caeiro@gmail.com',
        'le_19camargo@hotmail.com',
        'heloheloisa.srf@gmail.com',
    ];

    $subject = sprintf(
        '[SUED Studio] 🚀 Novo Diagnóstico #%d — %s (%s) — %s pts',
        $lead_id,
        $lead_data['business_name'] ?: $lead_data['name'],
        $lead_data['profile'] ?: 'Diagnóstico',
        $lead_data['score']
    );

    $admin_lead_url = admin_url('admin.php?page=sued-quiz-leads&lead_id=' . $lead_id);
    $wa_clean = preg_replace('/\D+/', '', $lead_data['phone']);
    $wa_url = $wa_clean ? 'https://wa.me/' . (str_starts_with($wa_clean, '55') ? $wa_clean : '55' . $wa_clean) : '';

    // HTML Email Template matching SUED Studio design
    $html  = '<!DOCTYPE html>';
    $html .= '<html lang="pt-BR"><head><meta charset="UTF-8"><title>Novo Diagnóstico SUED Studio</title></head>';
    $html .= '<body style="background-color:#0A1118; color:#F5F2EC; font-family:\'Montserrat\',Arial,sans-serif; margin:0; padding:24px;">';
    $html .= '<table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#0F1923; border:1px solid #2A3A47; border-radius:12px; overflow:hidden;">';
    
    // Header
    $html .= '<tr><td style="padding:28px 32px; background-color:#141F2B; border-bottom:1px solid #2A3A47;">';
    $html .= '<div style="font-size:16px; font-weight:700; letter-spacing:2px; color:#F5F2EC; text-transform:uppercase;">SUED<span style="color:#26AFFF;">.</span>STUDIO</div>';
    $html .= '<div style="color:#26AFFF; font-size:12px; font-weight:600; text-transform:uppercase; margin-top:4px; letter-spacing:1px;">Novo Lead do Diagnóstico Digital</div>';
    $html .= '</td></tr>';

    // Summary Card
    $html .= '<tr><td style="padding:32px;">';
    $html .= '<div style="background-color:#141F2B; border:1px solid #2A3A47; border-radius:8px; padding:20px; text-align:center; margin-bottom:24px;">';
    $html .= '<div style="font-size:32px; font-weight:700; color:#26AFFF; line-height:1;">' . esc_html($lead_data['score']) . ' <span style="font-size:14px; color:#7A8B95;">pontos</span></div>';
    $html .= '<div style="font-size:14px; font-weight:700; color:#C8A96E; text-transform:uppercase; letter-spacing:1px; margin-top:6px;">' . esc_html($lead_data['profile']) . '</div>';
    $html .= '</div>';

    // Lead Details Table
    $html .= '<table width="100%" cellpadding="8" cellspacing="0" style="font-size:14px; color:#F5F2EC; margin-bottom:24px; border-collapse:collapse;">';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td width="35%" style="color:#7A8B95; font-weight:600;">Negócio / Empresa:</td><td style="font-weight:700;">' . esc_html($lead_data['business_name']) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">Responsável:</td><td>' . esc_html($lead_data['name']) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">E-mail:</td><td><a href="mailto:' . esc_attr($lead_data['email']) . '" style="color:#26AFFF; text-decoration:none;">' . esc_html($lead_data['email']) . '</a></td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">WhatsApp:</td><td>' . ($wa_url ? '<a href="' . esc_url($wa_url) . '" style="color:#26AFFF; text-decoration:none;">' . esc_html($lead_data['phone']) . ' ↗ (Abrir no WhatsApp)</a>' : esc_html($lead_data['phone'] ?: '—')) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">Setor:</td><td>' . esc_html($lead_data['answers']['setor']['label'] ?? $lead_data['sector']) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">Presença Digital:</td><td>' . esc_html($lead_data['answers']['presenca']['label'] ?? $lead_data['presence']) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">Mídia / Tráfego:</td><td>' . esc_html($lead_data['answers']['trafego']['label'] ?? $lead_data['traffic']) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">Objetivo 90 dias:</td><td>' . esc_html($lead_data['answers']['objetivo']['label'] ?? $lead_data['goal']) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">Investimento / Mês:</td><td>' . esc_html($lead_data['answers']['budget']['label'] ?? $lead_data['budget']) . '</td></tr>';
    $html .= '<tr style="border-bottom:1px solid #1E2D3B;"><td style="color:#7A8B95; font-weight:600;">Maior Frustração:</td><td>' . esc_html($lead_data['answers']['dor']['label'] ?? $lead_data['pain']) . '</td></tr>';
    $html .= '<tr><td style="color:#7A8B95; font-weight:600;">Data / Hora:</td><td>' . esc_html(current_time('d/m/Y H:i:s')) . '</td></tr>';
    $html .= '</table>';

    // Call to Action Buttons
    $html .= '<div style="text-align:center; margin-top:28px;">';
    $html .= '<a href="' . esc_url($admin_lead_url) . '" style="display:inline-block; background-color:#26AFFF; color:#0F1923; font-size:14px; font-weight:700; text-decoration:none; padding:12px 24px; border-radius:8px; margin-right:8px;">Ver Lead no CRM do WordPress →</a>';
    if ($wa_url) {
        $html .= '<a href="' . esc_url($wa_url) . '" style="display:inline-block; background-color:#1E2D3B; border:1px solid #2A3A47; color:#F5F2EC; font-size:14px; font-weight:600; text-decoration:none; padding:12px 20px; border-radius:8px;">Falar no WhatsApp</a>';
    }
    $html .= '</div>';

    $html .= '</td></tr>';
    $html .= '<tr><td style="padding:16px 32px; background-color:#0A1118; font-size:11px; color:#7A8B95; text-align:center; border-top:1px solid #1E2D3B;">';
    $html .= 'SUED Studio © ' . date('Y') . ' · Notificação automática de novo diagnóstico digital.';
    $html .= '</td></tr>';
    $html .= '</table>';
    $html .= '</body></html>';

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: SUED Studio <contato@suedstudio.com.br>',
        sprintf('Reply-To: %s <%s>', $lead_data['name'], $lead_data['email']),
    ];

    wp_mail($recipients, $subject, $html, $headers);
}

/* ─── 4. WP-Admin CRM — Submenu under Leads SUED ───────────────── */

add_action('admin_menu', function (): void {
    add_submenu_page(
        'sued-leads',
        __('Quiz Diagnóstico', 'sued-studio'),
        __('Quiz Diagnóstico', 'sued-studio'),
        'manage_options',
        'sued-quiz-leads',
        'sued_render_quiz_crm_page'
    );
}, 20);

/**
 * Render modern dark-mode CRM dashboard in WP-Admin matching SUED Studio branding.
 */
function sued_render_quiz_crm_page(): void {
    if (!current_user_can('manage_options')) {
        wp_die(__('Acesso negado.', 'sued-studio'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'sued_quiz_leads';

    // Status action
    if (
        isset($_GET['action'], $_GET['lead_id'], $_GET['_wpnonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sued_quiz_lead_action')
    ) {
        $lead_id    = absint($_GET['lead_id']);
        $new_status = sanitize_text_field(wp_unslash($_GET['action']));
        $allowed    = ['new', 'contacted', 'converted', 'disqualified'];

        if (in_array($new_status, $allowed, true)) {
            $wpdb->update($table, ['status' => $new_status], ['id' => $lead_id], ['%s'], ['%d']);
        }
    }

    // Filter by status
    $filter_status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
    $where         = $filter_status ? $wpdb->prepare('WHERE status = %s', $filter_status) : '';
    $leads         = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY created_at DESC");

    // Single lead view
    $view_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
    $lead    = $view_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $view_id)) : null;

    // Counts
    $counts_raw = $wpdb->get_results("SELECT status, COUNT(*) as total FROM {$table} GROUP BY status", OBJECT_K);
    $all_statuses = [
        'new'           => ['label' => 'Novos',          'icon' => '⚡', 'color' => '#26AFFF'],
        'contacted'     => ['label' => 'Contactados',    'icon' => '📞', 'color' => '#C8A96E'],
        'converted'     => ['label' => 'Convertidos',    'icon' => '💎', 'color' => '#00E5A3'],
        'disqualified'  => ['label' => 'Desqualificados','icon' => '✖',  'color' => '#FF4D4D'],
    ];

    $total_all = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    ?>

    <style>
      #wpcontent { background: #0A1118; color: #F5F2EC; }
      .sued-crm-wrap {
        max-width: 1300px;
        margin: 20px 20px 40px 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
      }
      .sued-crm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        border-bottom: 1px solid #2A3A47;
        padding-bottom: 18px;
      }
      .sued-crm-title {
        font-size: 26px;
        font-weight: 700;
        color: #F5F2EC;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
      }
      .sued-crm-title span { color: #26AFFF; }
      .sued-badge-count {
        background: #141F2B;
        border: 1px solid #2A3A47;
        color: #26AFFF;
        font-size: 13px;
        padding: 4px 10px;
        border-radius: 20px;
      }
      .sued-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 28px;
      }
      .sued-stat-card {
        background: #0F1923;
        border: 1px solid #2A3A47;
        border-radius: 12px;
        padding: 16px 20px;
        text-decoration: none;
        color: #F5F2EC;
        transition: transform 0.15s, border-color 0.15s;
        display: block;
      }
      .sued-stat-card:hover, .sued-stat-card.active {
        border-color: #26AFFF;
        transform: translateY(-2px);
      }
      .sued-stat-num {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
      }
      .sued-stat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #7A8B95;
      }
      .sued-crm-table {
        width: 100%;
        border-collapse: collapse;
        background: #0F1923;
        border: 1px solid #2A3A47;
        border-radius: 12px;
        overflow: hidden;
      }
      .sued-crm-table th {
        background: #141F2B;
        color: #7A8B95;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #2A3A47;
      }
      .sued-crm-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #1E2D3B;
        font-size: 14px;
        color: #F5F2EC;
        vertical-align: middle;
      }
      .sued-crm-table tr:hover td { background: #141F2B; }
      .sued-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
      }
      .sued-pill-new { background: rgba(38,175,255,0.15); color: #26AFFF; border: 1px solid rgba(38,175,255,0.3); }
      .sued-pill-contacted { background: rgba(200,169,110,0.15); color: #C8A96E; border: 1px solid rgba(200,169,110,0.3); }
      .sued-pill-converted { background: rgba(0,229,163,0.15); color: #00E5A3; border: 1px solid rgba(0,229,163,0.3); }
      .sued-pill-disqualified { background: rgba(255,77,77,0.15); color: #FF4D4D; border: 1px solid rgba(255,77,77,0.3); }

      .sued-score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(38,175,255,0.12);
        color: #26AFFF;
        font-weight: 700;
        border: 1px solid rgba(38,175,255,0.3);
      }

      .sued-detail-box {
        background: #0F1923;
        border: 1px solid #2A3A47;
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 28px;
        position: relative;
      }
      .sued-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
      }
      @media(max-width: 800px) {
        .sued-detail-grid { grid-template-columns: 1fr; }
      }
      .sued-ans-item {
        background: #141F2B;
        border: 1px solid #2A3A47;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
      }
      .sued-ans-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #7A8B95;
        letter-spacing: 0.08em;
        margin-bottom: 4px;
      }
      .sued-ans-val {
        font-size: 14px;
        color: #F5F2EC;
        font-weight: 500;
      }
      .sued-btn-action {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: opacity 0.15s;
        border: 1px solid transparent;
      }
      .sued-btn-action:hover { opacity: 0.85; }
    </style>

    <div class="sued-crm-wrap">
        <div class="sued-crm-header">
            <div>
                <h1 class="sued-crm-title">SUED<span>.</span>Studio — CRM Quiz Leads</h1>
                <p style="color:#7A8B95; margin:6px 0 0; font-size:13px;">Respostas em tempo real dos leads que concluíram o diagnóstico digital.</p>
            </div>
            <div>
                <span class="sued-badge-count"><?php echo esc_html($total_all); ?> leads totais</span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="sued-stats-grid">
            <a href="<?php echo esc_url(admin_url('admin.php?page=sued-quiz-leads')); ?>" class="sued-stat-card <?php echo !$filter_status ? 'active' : ''; ?>">
                <div class="sued-stat-num" style="color:#F5F2EC;"><?php echo esc_html($total_all); ?></div>
                <div class="sued-stat-label">Todos os Leads</div>
            </a>
            <?php foreach ($all_statuses as $st_key => $st_info) :
                $st_count = isset($counts_raw[$st_key]) ? (int)$counts_raw[$st_key]->total : 0;
            ?>
            <a href="<?php echo esc_url(add_query_arg(['page' => 'sued-quiz-leads', 'status' => $st_key], admin_url('admin.php'))); ?>"
               class="sued-stat-card <?php echo $filter_status === $st_key ? 'active' : ''; ?>">
                <div class="sued-stat-num" style="color:<?php echo esc_attr($st_info['color']); ?>;">
                    <?php echo esc_html($st_count); ?>
                </div>
                <div class="sued-stat-label"><?php echo esc_html($st_info['icon'] . ' ' . $st_info['label']); ?></div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($lead) : 
            $answers = json_decode($lead->answers_json, true) ?: [];
            $wa_num = preg_replace('/\D+/', '', $lead->phone);
            $wa_link = $wa_num ? 'https://wa.me/' . (str_starts_with($wa_num, '55') ? $wa_num : '55' . $wa_num) : '';
        ?>
        <!-- Single Lead Detail View -->
        <div class="sued-detail-box">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; border-bottom:1px solid #2A3A47; padding-bottom:16px; flex-wrap:wrap; gap:12px;">
                <div>
                    <span class="sued-pill sued-pill-<?php echo esc_attr($lead->status); ?>"><?php echo esc_html($all_statuses[$lead->status]['label'] ?? $lead->status); ?></span>
                    <h2 style="font-size:22px; margin:8px 0 4px; color:#F5F2EC;">
                        <?php echo esc_html($lead->business_name ?: $lead->name); ?>
                    </h2>
                    <p style="color:#7A8B95; margin:0; font-size:13px;">
                        Contato: <strong><?php echo esc_html($lead->name); ?></strong> · Enviado em <?php echo esc_html(date_i18n('d/m/Y \à\s H:i', strtotime($lead->created_at))); ?>
                    </p>
                </div>
                <div style="text-align:right;">
                    <div class="sued-score-badge" style="width:48px; height:48px; font-size:18px;">
                        <?php echo esc_html($lead->score); ?>
                    </div>
                    <div style="font-size:11px; text-transform:uppercase; color:#C8A96E; font-weight:700; margin-top:4px;">
                        <?php echo esc_html($lead->profile); ?>
                    </div>
                </div>
            </div>

            <div class="sued-detail-grid">
                <!-- Coluna 1: Dados de Contato e Ações -->
                <div>
                    <h3 style="font-size:14px; text-transform:uppercase; color:#7A8B95; letter-spacing:0.08em; margin-bottom:14px;">Dados de Contato</h3>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">E-mail</div>
                        <div class="sued-ans-val">
                            <a href="mailto:<?php echo esc_attr($lead->email); ?>" style="color:#26AFFF; text-decoration:none;">
                                <?php echo esc_html($lead->email); ?> ↗
                            </a>
                        </div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">WhatsApp / Telefone</div>
                        <div class="sued-ans-val">
                            <?php if ($wa_link) : ?>
                                <a href="<?php echo esc_url($wa_link); ?>" target="_blank" style="color:#00E5A3; text-decoration:none; font-weight:600;">
                                    <?php echo esc_html($lead->phone); ?> ↗ (Chamar no WhatsApp)
                                </a>
                            <?php else : ?>
                                <?php echo esc_html($lead->phone ?: 'Não informado'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">Consentimento LGPD</div>
                        <div class="sued-ans-val" style="color:#00E5A3;">
                            ✓ Aceito pelo lead no momento da submissão
                        </div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">Origem / IP</div>
                        <div class="sued-ans-val" style="color:#7A8B95; font-size:12px;">
                            <?php echo esc_html($lead->source ?: 'Direto'); ?> · IP: <?php echo esc_html($lead->ip_address); ?>
                        </div>
                    </div>

                    <h3 style="font-size:14px; text-transform:uppercase; color:#7A8B95; letter-spacing:0.08em; margin:20px 0 10px;">Atualizar Status</h3>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <?php foreach ($all_statuses as $st_key => $st_info) : 
                            $status_url = wp_nonce_url(
                                add_query_arg(['page' => 'sued-quiz-leads', 'lead_id' => $lead->id, 'action' => $st_key], admin_url('admin.php')),
                                'sued_quiz_lead_action'
                            );
                        ?>
                        <a href="<?php echo esc_url($status_url); ?>" 
                           class="sued-btn-action" 
                           style="background:<?php echo esc_attr($lead->status === $st_key ? $st_info['color'] : '#141F2B'); ?>; color:<?php echo esc_attr($lead->status === $st_key ? '#0F1923' : '#F5F2EC'); ?>; border:1px solid #2A3A47;">
                            <?php echo esc_html($st_info['icon'] . ' ' . $st_info['label']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Coluna 2: Respostas do Diagnóstico -->
                <div>
                    <h3 style="font-size:14px; text-transform:uppercase; color:#7A8B95; letter-spacing:0.08em; margin-bottom:14px;">Respostas do Quiz</h3>
                    
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">1. Setor de Atuação</div>
                        <div class="sued-ans-val"><?php echo esc_html($answers['setor']['label'] ?? $lead->sector); ?></div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">2. Maturidade da Presença Digital</div>
                        <div class="sued-ans-val"><?php echo esc_html($answers['presenca']['label'] ?? $lead->presence); ?></div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">3. Investimento em Mídia / Tráfego Pago</div>
                        <div class="sued-ans-val"><?php echo esc_html($answers['trafego']['label'] ?? $lead->traffic); ?></div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">4. Principal Objetivo para os Próximos 90 Dias</div>
                        <div class="sued-ans-val"><?php echo esc_html($answers['objetivo']['label'] ?? $lead->goal); ?></div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">5. Orçamento Mensal Disponível</div>
                        <div class="sued-ans-val"><?php echo esc_html($answers['budget']['label'] ?? $lead->budget); ?></div>
                    </div>
                    <div class="sued-ans-item">
                        <div class="sued-ans-title">6. Maior Frustração Atual</div>
                        <div class="sued-ans-val"><?php echo esc_html($answers['dor']['label'] ?? $lead->pain); ?></div>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px; text-align:right;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=sued-quiz-leads')); ?>" style="color:#7A8B95; text-decoration:none; font-size:13px;">
                    &larr; Fechar detalhes e voltar para lista completa
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Table of leads -->
        <table class="sued-crm-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Negócio / Lead</th>
                    <th>Contato</th>
                    <th>Setor</th>
                    <th>Score / Perfil</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th width="100">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)) : ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:32px; color:#7A8B95;">
                        Nenhum lead encontrado neste filtro.
                    </td>
                </tr>
                <?php else : ?>
                <?php foreach ($leads as $l) : 
                    $l_answers = json_decode($l->answers_json, true) ?: [];
                    $l_wa = preg_replace('/\D+/', '', $l->phone);
                ?>
                <tr>
                    <td style="color:#7A8B95;">#<?php echo esc_html($l->id); ?></td>
                    <td>
                        <strong style="color:#F5F2EC; font-size:15px;"><?php echo esc_html($l->business_name ?: $l->name); ?></strong>
                        <?php if ($l->business_name && $l->name) : ?>
                            <div style="font-size:12px; color:#7A8B95;"><?php echo esc_html($l->name); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div><a href="mailto:<?php echo esc_attr($l->email); ?>" style="color:#26AFFF; text-decoration:none;"><?php echo esc_html($l->email); ?></a></div>
                        <?php if ($l->phone) : ?>
                            <div style="font-size:12px; color:#7A8B95;">
                                <?php if ($l_wa) : ?>
                                    <a href="https://wa.me/<?php echo esc_attr(str_starts_with($l_wa, '55') ? $l_wa : '55' . $l_wa); ?>" target="_blank" style="color:#00E5A3; text-decoration:none;">
                                        <?php echo esc_html($l->phone); ?> ↗
                                    </a>
                                <?php else : ?>
                                    <?php echo esc_html($l->phone); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px; color:#F5F2EC;">
                        <?php echo esc_html($l_answers['setor']['label'] ?? $l->sector ?: '—'); ?>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="sued-score-badge"><?php echo esc_html($l->score); ?></span>
                            <span style="font-size:12px; color:#C8A96E; font-weight:600;"><?php echo esc_html($l->profile); ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="sued-pill sued-pill-<?php echo esc_attr($l->status); ?>">
                            <?php echo esc_html($all_statuses[$l->status]['label'] ?? $l->status); ?>
                        </span>
                    </td>
                    <td style="font-size:12px; color:#7A8B95;">
                        <?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($l->created_at))); ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url(add_query_arg(['page' => 'sued-quiz-leads', 'lead_id' => $l->id], admin_url('admin.php'))); ?>"
                           class="sued-btn-action"
                           style="background:#141F2B; border:1px solid #2A3A47; color:#26AFFF;">
                            Ver lead
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/* ─── 5. Shortcode Support [sued_quiz] ─────────────────────────── */

add_shortcode('sued_quiz', function (): string {
    ob_start();
    include SUED_DIR . '/blocks/quiz/template.php';
    return (string) ob_get_clean();
});
