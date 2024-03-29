(function (window) {
    /**
     * Update a single config value.
     * 
     * @param {string} name Key of config to update.
     * @param {any} value New value.
     */
    function updateConfig(name, value) {
        window.wp.data.dispatch('WPTravel/Admin').updateSettings({
            [name]: value
        });
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
        if (!props.show && props.show !== undefined)
            return null;

        let Input        = props.type == 'control' ? window.wp.components.ToggleControl : window.wp.components.TextControl;
        let description  = props.description ? <p className="description">{window.wp.i18n.__(props.description, 'wp-travel-mobbex')}</p> : null;

        return (
            <window.wp.components.PanelRow>
                <label>{window.wp.i18n.__(props.label, 'wp-travel-mobbex')}</label>
                <div className="wp-travel-field-value">
                    <Input value={props.type != 'control' && props.value} checked={props.value} onChange={value => updateConfig(props.name, value)}/>
                    {description}
                </div>
            </window.wp.components.PanelRow>
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
        return (
            <React.Fragment>
                <Option type="control" name="payment_option_mobbex" value={props.configs.payment_option_mobbex} label="Activar Mobbex" description="Haga clic para activar la opción de pago"/>
                <Option type="control" name="mobbex_test" value={props.configs.mobbex_test} label="Activar modo de pruebas" show={props.configs.payment_option_mobbex}/>
                <Option type="control" name="mobbex_debug_mode" value={props.configs.mobbex_debug_mode} label="Activar modo de depuración" show={props.configs.payment_option_mobbex}/>
                <Option type="text" name="mobbex_api_key" value={props.configs.mobbex_api_key} label="Clave de API" show={props.configs.payment_option_mobbex}/>
                <Option type="text" name="mobbex_access_token" value={props.configs.mobbex_access_token} label="Token de acceso" show={props.configs.payment_option_mobbex}/>
                <Option type="control" name="mobbex_finance_trip" value={props.configs.mobbex_finance_trip} label="Financiación en Viaje" description="Mostrar el botón de financiación con planes de pago en la página del viaje." show={props.configs.payment_option_mobbex}/>
                <Option type="control" name="mobbex_multicard" value={props.configs.mobbex_multicard} label="Multicard" description="Permite pagar usando múltiples tarjetas" show={props.configs.payment_option_mobbex}/>
            </React.Fragment>
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
        return [
            ...components,
            <Form configs={configs}/>
        ];
    }

    window.addEventListener('load', () => window.wp.hooks.addFilter('wp_travel_payment_gateway_fields_mobbex', 'mobbex-settings', renderSettings));
}) (window);