<?php
/**
 * SUED Studio — Contact Form Handler
 *
 * Responsibilities:
 *  1. Register a custom DB table `{prefix}sued_leads` on theme activation.
 *  2. Handle AJAX submission (nopriv + priv) — validate, sanitise, persist, email.
 *  3. Register a WP-Admin menu page so leads are accessible from wp-admin.
 *
 * @package SUED_Studio
 */

defined('ABSPATH') || exit;

/* ─── 1. Database — create table on activation ─────────────────── */

function sued_create_leads_table(): void {
    global $wpdb;

    $table   = $wpdb->prefix . 'sued_leads';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name        VARCHAR(150)        NOT NULL DEFAULT '',
        email       VARCHAR(200)        NOT NULL DEFAULT '',
        phone       VARCHAR(30)         NOT NULL DEFAULT '',
        message     TEXT                NOT NULL,
        source      VARCHAR(255)        NOT NULL DEFAULT '',
        ip_address  VARCHAR(45)         NOT NULL DEFAULT '',
        user_agent  VARCHAR(500)        NOT NULL DEFAULT '',
        status      ENUM('new','contacted','converted','disqualified') NOT NULL DEFAULT 'new',
        created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY email (email),
        KEY status (status),
        KEY created_at (created_at)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('sued_leads_db_version', '1.0');
}
add_action('after_switch_theme', 'sued_create_leads_table');

/* Also ensure table exists on init (idempotent) */
add_action('init', function (): void {
    if ( get_option('sued_leads_db_version') !== '1.0' ) {
        sued_create_leads_table();
    }
});

/* ─── 2. AJAX Handler ───────────────────────────────────────────── */

/**
 * Common handler for both logged-in and guest users.
 */
function sued_handle_contact_form(): void {
    /* ── Nonce verification ── */
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if ( ! wp_verify_nonce($nonce, 'sued_nonce') ) {
        wp_send_json_error(['message' => __('Requisição inválida.', 'sued-studio')], 403);
    }

    /* ── Input sanitisation ── */
    $name    = sanitize_text_field(wp_unslash($_POST['name']    ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['email']        ?? ''));
    $phone   = sanitize_text_field(wp_unslash($_POST['phone']   ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    $source  = esc_url_raw(wp_unslash($_POST['source'] ?? wp_get_referer() ?: home_url()));

    /* ── Validation ── */
    $errors = [];
    if ( strlen($name) < 2 ) {
        $errors[] = __('Por favor, informe seu nome completo.', 'sued-studio');
    }
    if ( ! is_email($email) ) {
        $errors[] = __('Por favor, informe um e-mail válido.', 'sued-studio');
    }
    if ( ! empty($errors) ) {
        wp_send_json_error(['message' => implode(' ', $errors)], 422);
    }

    /* ── Honeypot (spam protection) ── */
    if ( ! empty($_POST['website']) ) {
        // Silently succeed to confuse bots
        wp_send_json_success(['message' => __('Mensagem enviada! Retornamos em breve.', 'sued-studio')]);
    }

    /* ── Persist to DB ── */
    global $wpdb;
    $table = $wpdb->prefix . 'sued_leads';

    $ip         = sanitize_text_field(
        wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '')
    );
    $user_agent = sanitize_text_field(
        wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')
    );

    $inserted = $wpdb->insert(
        $table,
        [
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'message'    => $message,
            'source'     => $source,
            'ip_address' => $ip,
            'user_agent' => $user_agent,
            'status'     => 'new',
            'created_at' => current_time('mysql'),
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
    );

    if ( false === $inserted ) {
        wp_send_json_error(['message' => __('Erro interno. Tente novamente.', 'sued-studio')], 500);
    }

    $lead_id = $wpdb->insert_id;

    /* ── Send notification email ── */
    $to      = 'contato@suedstudio.com.br';
    $subject = sprintf('[SUED Studio] Novo lead #%d — %s', $lead_id, $name);

    $body  = "Novo lead recebido pelo formulário de contato do site.\n\n";
    $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $body .= sprintf("ID do Lead : #%d\n", $lead_id);
    $body .= sprintf("Nome       : %s\n", $name);
    $body .= sprintf("E-mail     : %s\n", $email);
    $body .= sprintf("Telefone   : %s\n", $phone ?: '—');
    $body .= sprintf("Mensagem   : %s\n", $message ?: '—');
    $body .= sprintf("Origem     : %s\n", $source);
    $body .= sprintf("IP         : %s\n", $ip);
    $body .= sprintf("Data/Hora  : %s\n", current_time('d/m/Y H:i:s'));
    $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $body .= sprintf(
        "Visualizar no painel: %s\n",
        admin_url('admin.php?page=sued-leads&lead_id=' . $lead_id)
    );

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $name, $email),
    ];

    wp_mail($to, $subject, $body, $headers);

    wp_send_json_success([
        'message' => __('Mensagem enviada! Retornamos em breve.', 'sued-studio'),
        'lead_id' => $lead_id,
    ]);
}
add_action('wp_ajax_sued_contact',        'sued_handle_contact_form');
add_action('wp_ajax_nopriv_sued_contact', 'sued_handle_contact_form');

/* ─── 3. WP-Admin — Leads management page ──────────────────────── */

add_action('admin_menu', function (): void {
    add_menu_page(
        __('Leads SUED', 'sued-studio'),
        __('Leads SUED', 'sued-studio'),
        'manage_options',
        'sued-leads',
        'sued_render_leads_page',
        'dashicons-email-alt',
        30
    );
});

function sued_render_leads_page(): void {
    if ( ! current_user_can('manage_options') ) {
        wp_die(__('Acesso negado.', 'sued-studio'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'sued_leads';

    /* ── Status update action ── */
    if (
        isset($_GET['action'], $_GET['lead_id'], $_GET['_wpnonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sued_lead_action')
    ) {
        $lead_id    = absint($_GET['lead_id']);
        $new_status = sanitize_text_field(wp_unslash($_GET['action']));
        $allowed    = ['new', 'contacted', 'converted', 'disqualified'];

        if ( in_array($new_status, $allowed, true) ) {
            $wpdb->update($table, ['status' => $new_status], ['id' => $lead_id], ['%s'], ['%d']);
        }
    }

    /* ── Filter by status ── */
    $filter_status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
    $where         = $filter_status ? $wpdb->prepare('WHERE status = %s', $filter_status) : '';
    $leads         = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY created_at DESC");

    /* ── Single lead view ── */
    $view_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
    $lead    = $view_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $view_id)) : null;

    $status_labels = [
        'new'           => '🆕 Novo',
        'contacted'     => '📞 Contactado',
        'converted'     => '✅ Convertido',
        'disqualified'  => '❌ Desqualificado',
    ];

    /* ── Total counts ── */
    $counts = $wpdb->get_results("SELECT status, COUNT(*) as total FROM {$table} GROUP BY status", OBJECT_K);

    ?>
    <div class="wrap">
        <h1>📬 Leads SUED Studio</h1>

        <!-- Summary cards -->
        <div style="display:flex;gap:1rem;margin:1.5rem 0;flex-wrap:wrap;">
            <?php
            $all_statuses = ['new' => '🆕 Novos', 'contacted' => '📞 Contactados', 'converted' => '✅ Convertidos', 'disqualified' => '❌ Desqualificados'];
            foreach ($all_statuses as $s => $label) :
                $count = isset($counts[$s]) ? (int) $counts[$s]->total : 0;
                $bg    = $s === 'new' ? '#0073aa' : ($s === 'converted' ? '#00a32a' : ($s === 'disqualified' ? '#d63638' : '#50575e'));
            ?>
            <a href="<?php echo esc_url(add_query_arg(['page' => 'sued-leads', 'status' => $s], admin_url('admin.php'))); ?>"
               style="text-decoration:none;">
                <div style="background:<?php echo esc_attr($bg); ?>;color:#fff;padding:1rem 1.5rem;border-radius:6px;min-width:140px;text-align:center;">
                    <div style="font-size:2rem;font-weight:700;"><?php echo esc_html($count); ?></div>
                    <div style="font-size:0.8rem;margin-top:0.25rem;"><?php echo esc_html($label); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($lead) : ?>
        <!-- Single lead detail view -->
        <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:2rem;max-width:700px;margin-bottom:2rem;">
            <h2 style="margin-top:0;">Lead #<?php echo esc_html($lead->id); ?> — <?php echo esc_html($lead->name); ?></h2>
            <table class="widefat striped" style="margin-bottom:1.5rem;">
                <tr><th width="150">Nome</th><td><?php echo esc_html($lead->name); ?></td></tr>
                <tr><th>E-mail</th><td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td></tr>
                <tr><th>Telefone</th><td><?php echo esc_html($lead->phone ?: '—'); ?></td></tr>
                <tr><th>Mensagem</th><td><?php echo nl2br(esc_html($lead->message ?: '—')); ?></td></tr>
                <tr><th>Origem</th><td><?php echo esc_html($lead->source); ?></td></tr>
                <tr><th>IP</th><td><?php echo esc_html($lead->ip_address); ?></td></tr>
                <tr><th>Status</th><td><?php echo esc_html($status_labels[$lead->status] ?? $lead->status); ?></td></tr>
                <tr><th>Data</th><td><?php echo esc_html(date_i18n('d/m/Y H:i:s', strtotime($lead->created_at))); ?></td></tr>
            </table>

            <strong>Alterar status:</strong>
            <div style="display:flex;gap:0.5rem;margin-top:0.5rem;flex-wrap:wrap;">
                <?php foreach (['contacted', 'converted', 'disqualified', 'new'] as $s) :
                    $url = wp_nonce_url(
                        add_query_arg(['page' => 'sued-leads', 'lead_id' => $lead->id, 'action' => $s], admin_url('admin.php')),
                        'sued_lead_action'
                    );
                ?>
                <a href="<?php echo esc_url($url); ?>" class="button"><?php echo esc_html($status_labels[$s]); ?></a>
                <?php endforeach; ?>
            </div>

            <p style="margin-top:1rem;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=sued-leads')); ?>">&larr; Voltar para a lista</a>
            </p>
        </div>
        <?php endif; ?>

        <!-- Leads table -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)) : ?>
                <tr><td colspan="7" style="text-align:center;padding:2rem;">Nenhum lead encontrado.</td></tr>
                <?php else : ?>
                <?php foreach ($leads as $l) : ?>
                <tr>
                    <td><?php echo esc_html($l->id); ?></td>
                    <td><strong><?php echo esc_html($l->name); ?></strong></td>
                    <td><a href="mailto:<?php echo esc_attr($l->email); ?>"><?php echo esc_html($l->email); ?></a></td>
                    <td><?php echo esc_html($l->phone ?: '—'); ?></td>
                    <td><?php echo esc_html($status_labels[$l->status] ?? $l->status); ?></td>
                    <td><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($l->created_at))); ?></td>
                    <td>
                        <a href="<?php echo esc_url(add_query_arg(['page' => 'sued-leads', 'lead_id' => $l->id], admin_url('admin.php'))); ?>">
                            Ver detalhes
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($filter_status) : ?>
        <p><a href="<?php echo esc_url(admin_url('admin.php?page=sued-leads')); ?>">&larr; Ver todos os leads</a></p>
        <?php endif; ?>
    </div>
    <?php
}
