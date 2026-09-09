/**
 * SUED Studio — Editor block registration (no build step)
 * Registers all custom blocks in the JS block registry for the Site Editor.
 */
(function () {
    'use strict';

    if (!window.wp || !window.wp.blocks || !window.wp.element) return;

    var blocks = window.wp.blocks;
    var el     = window.wp.element.createElement;

    var defs = [
        { name: 'sued-studio/hero',         label: 'Hero — Three.js' },
        { name: 'sued-studio/positioning',  label: 'Posicionamento' },
        { name: 'sued-studio/process',      label: 'Como Trabalhamos' },
        { name: 'sued-studio/services',     label: 'Serviços' },
        { name: 'sued-studio/results',      label: 'Resultados' },
        { name: 'sued-studio/differential', label: 'Diferencial' },
        { name: 'sued-studio/cta',          label: 'CTA Final' },
    ];

    defs.forEach(function (def) {
        // Skip if PHP auto-registration already handled it
        if (blocks.getBlockType(def.name)) return;

        blocks.registerBlockType(def.name, {
            title:    def.label,
            category: 'sued-studio',
            icon:     'layout',
            attributes: {
                align: { type: 'string', default: 'full' }
            },
            supports: { html: false, align: ['full'] },

            edit: function () {
                return el('div', {
                    style: {
                        padding:       '2rem',
                        background:    '#0F1923',
                        color:         '#F5F2EC',
                        textAlign:     'center',
                        border:        '1px dashed rgba(38,175,255,0.5)',
                        borderRadius:  '4px',
                        fontFamily:    'monospace',
                        fontSize:      '0.85rem',
                        opacity:       '0.9',
                    }
                }, '⚡ SUED Studio — ' + def.label + ' (renderizado no servidor)');
            },

            // null = dynamic/server-side rendered, no client-side save
            save: function () { return null; },
        });
    });
}());
