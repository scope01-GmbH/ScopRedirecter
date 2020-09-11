// {namespace name="backend/scop_redirecter/view/detail/redirecter"}
// {block name="backend/scop_redirecter/view/detail/redirecter"}
Ext.define('Shopware.apps.ScopRedirecter.view.detail.Redirecter', {
    extend: 'Shopware.model.Container',
    alias: 'widget.scop-redirecter-detail-container',
    label301: '{s name=label_301}301 (Moved Permanently){/s}',
    label302: '{s name=label_302}302 (Found / Moved Temporarily){/s}',

    configure: function() {
        var me = this;
        return {
            controller: 'ScopRedirecter',
            fieldSets: [{
                title: '{s name=field_set}Edit redirect{/s}',
                layout: 'fit',
                fields: {
                    startUrl: { fieldLabel: '{s name=start_url}Start URI{/s}' },
                    targetUrl: { fieldLabel: '{s name=target_url}Target URI{/s}' },
                    httpCode: {
                        fieldLabel: '{s name=http_code}Http Code{/s}',
                        type: "int",
                        xtype: 'combobox',
                        queryMode: 'local',
                        displayField: 'name',
                        valueField: 'id',
                        store: me.getStore(),
                        'listeners': {
                            'change': function (combobox, newValue) {
                                record = me.getStore().findRecord('id', newValue, 0, false, false, true);
                            }
                        }
                    }
                }
            }],
        };
    },
    'getStore': function () {
        var me = this;

        if (!(me.store instanceof Ext.data.Store)) {
            //{literal}
            me.store = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data: [
                    {id: 301, name: me.label301},
                    {id: 302, name: me.label302}
                ]
            });
            //{/literal}
        }
        return me.store;
    }
});
// {/block}


