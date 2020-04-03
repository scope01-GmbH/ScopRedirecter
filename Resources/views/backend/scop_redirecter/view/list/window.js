// {namespace name="backend/scop_redirecter/view/list/window"}
// {block name="backend/scop_redirecter/view/list/window"}
Ext.define('Shopware.apps.ScopRedirecter.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.scop-redirecter-list-window',
    height: 450,
    title : '{s name=window_title}Redirect Listing{/s}',

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.ScopRedirecter.view.list.Redirecter',
            listingStore: 'Shopware.apps.ScopRedirecter.store.Redirecter'
        };
    },

});
// {/block}


