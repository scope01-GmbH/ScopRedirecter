Ext.define('Shopware.apps.ScopRedirecter.model.Redirecter', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'ScopRedirecter',
            detail: 'Shopware.apps.ScopRedirecter.view.detail.Redirecter',
        };

    },
    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'startUrl', type: 'string', allowBlank: false, hideLabel: true,},
        { name : 'targetUrl', type: 'string', allowBlank: false},
        { name : 'httpCode', type: 'integer', useNull: true, allowBlank: true}
    ]
});
