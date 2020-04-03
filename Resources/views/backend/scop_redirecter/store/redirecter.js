Ext.define('Shopware.apps.ScopRedirecter.store.Redirecter', {
    extend:'Shopware.store.Listing',

    configure: function() {
        return {
            controller: 'ScopRedirecter'
        };
    },
    model: 'Shopware.apps.ScopRedirecter.model.Redirecter'
});