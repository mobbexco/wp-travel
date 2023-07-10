function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

(function (window) {
    /**
     * Update a single config value.
     * 
     * @param {string} name Key of config to update.
     * @param {any} value New value.
     */
    function updateConfig(name, value) {
        window.wp.data.dispatch('WPTravel/Admin').updateSettings(_defineProperty({}, name, value));
    }

    /**
     * Option component.
     * 
     * @property {string} type
     * @property {string} name
     * @property {string} value
     * @property {string} label
     * @property {string|null} description
     * @property {bool|null} show Value to check for hide option.
     * 
     * @returns {React.Component}
     */
    function Option(props) {
        if (!props.show && props.show !== undefined) return null;

        var Input = props.type == 'control' ? window.wp.components.ToggleControl : window.wp.components.TextControl;
        var description = props.description ? React.createElement(
            'p',
            { className: 'description' },
            window.wp.i18n.__(props.description, 'wp-travel-mobbex')
        ) : null;

        return React.createElement(
            window.wp.components.PanelRow,
            null,
            React.createElement(
                'label',
                null,
                window.wp.i18n.__(props.label, 'wp-travel-mobbex')
            ),
            React.createElement(
                'div',
                { className: 'wp-travel-field-value' },
                React.createElement(Input, { value: props.type != 'control' && props.value, checked: props.value, onChange: function onChange(value) {
                        return updateConfig(props.name, value);
                    } }),
                description
            )
        );
    }

    /**
     * Form component.
     * 
     * @property {Object} configs Current configurated values.
     * 
     * @returns {React.Component}
     */
    function Form(props) {
        return React.createElement(
            React.Fragment,
            null,
            React.createElement(Option, { type: 'control', name: 'payment_option_mobbex', value: props.configs.payment_option_mobbex, label: 'Activar Mobbex', description: 'Haga clic para activar la opci\xF3n de pago' }),
            React.createElement(Option, { type: 'control', name: 'mobbex_test_mode', value: props.configs.mobbex_test_mode, label: 'Activar modo de pruebas', show: props.configs.payment_option_mobbex }),
            React.createElement(Option, { type: 'text', name: 'mobbex_api_key', value: props.configs.mobbex_api_key, label: 'Clave de API', show: props.configs.payment_option_mobbex }),
            React.createElement(Option, { type: 'text', name: 'mobbex_access_token', value: props.configs.mobbex_access_token, label: 'Token de acceso', show: props.configs.payment_option_mobbex }),
            React.createElement(Option, { type: 'control', name: 'mobbex_finance_trip', value: props.configs.mobbex_finance_trip, label: 'Financiaci\xF3n en Viaje', description: 'Mostrar el bot\xF3nde financiaci\xF3n con planes de pago en la p\xE1gina del viaje.' })
        );
    }

    /**
     * Render gateway settings form.
     * 
     * @param {React.Component[]} components Components queued for rendering.
     * @param {Object} configs Current configurated values.
     * 
     * @returns {React.Component[]}
     */
    function renderSettings(components, configs) {
        return [].concat(_toConsumableArray(components), [React.createElement(Form, { configs: configs })]);
    }

    window.addEventListener('load', function () {
        return window.wp.hooks.addFilter('wp_travel_payment_gateway_fields_mobbex', 'mobbex-settings', renderSettings);
    });
})(window);