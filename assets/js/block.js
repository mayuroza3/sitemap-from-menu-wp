( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
    var TextControl = wp.components.TextControl;
    var ServerSideRender = wp.serverSideRender;

    var menuOptions = window.sfm_block_data ? window.sfm_block_data.menus : [ { label: 'Loading...', value: '0' } ];

    registerBlockType( 'sitemap-from-menu/block', {
        title: 'Sitemap From Menu',
        icon: 'list-view',
        category: 'widgets',
        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return el(
                wp.element.Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Sitemap Settings', initialOpen: true },
                        el( SelectControl, {
                            label: 'Select Menu',
                            value: attributes.menu_id,
                            options: menuOptions,
                            onChange: function( val ) { setAttributes( { menu_id: val } ); }
                        } ),
                        el( ToggleControl, {
                            label: 'Include Nested Items',
                            checked: attributes.include_nested,
                            onChange: function( val ) { setAttributes( { include_nested: val } ); }
                        } ),
                        el( ToggleControl, {
                            label: 'Include Descriptions',
                            checked: attributes.include_desc,
                            onChange: function( val ) { setAttributes( { include_desc: val } ); }
                        } ),
                        el( TextControl, {
                            label: 'Heading Text (Optional)',
                            value: attributes.heading_text,
                            onChange: function( val ) { setAttributes( { heading_text: val } ); }
                        } ),
                        el( SelectControl, {
                            label: 'Heading Level',
                            value: attributes.heading_level,
                            options: [
                                { label: 'H2', value: 'h2' },
                                { label: 'H3', value: 'h3' },
                                { label: 'H4', value: 'h4' },
                                { label: 'H5', value: 'h5' },
                                { label: 'H6', value: 'h6' }
                            ],
                            onChange: function( val ) { setAttributes( { heading_level: val } ); }
                        } ),
                        el( TextControl, {
                            label: 'Container CSS Class',
                            value: attributes.container_class,
                            onChange: function( val ) { setAttributes( { container_class: val } ); }
                        } )
                    )
                ),
                el( ServerSideRender, {
                    block: 'sitemap-from-menu/block',
                    attributes: attributes
                } )
            );
        },
        save: function() {
            // Server-side rendered block.
            return null;
        }
    } );
} )( window.wp );
