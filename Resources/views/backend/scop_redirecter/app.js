//{namespace name="backend/bla"}

Ext.define('Shopware.apps.ScopRedirecter', {
    extend: 'Enlight.app.SubApplication',
    name:'Shopware.apps.ScopRedirecter',
    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main' ],

    views: [
        'list.Window',
        'list.Redirecter',

        'detail.Window',
        'detail.Redirecter'
    ],

    models: [ 'Redirecter' ],
    stores: [ 'Redirecter' ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
} );

