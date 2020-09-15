// {namespace name="backend/scop_redirecter/view/detail/redirecter"}
// {block name="backend/scop_redirecter/view/detail/redirecter"}
Ext.define('Shopware.apps.ScopRedirecter.view.detail.Redirecter', {
    extend: 'Shopware.model.Container',
    alias: 'widget.scop-redirecter-detail-container',
    label301: '{s name=label_301}301 (Moved Permanently){/s}',
    label302: '{s name=label_302}302 (Found / Moved Temporarily){/s}',

    initComponent: function() {
        let me = this;
        me.callParent(arguments);

        Shopware.app.Application.on('redirecter-save-successfully', function(me, result, window) {
            window.destroy();
        }, me, { single: true });
    },

    configure: function() {
        let me = this;

        return {
            controller: 'ScopRedirecter',
            fieldSets: [{
                title: '{s name=field_set}Edit redirect{/s}',
                layout: 'fit',
                fields: {
                    startUrl: {
                        allowBlank: false,
                        fieldLabel: '{s name=start_url}Start URI{/s}',
                        defaultValue: '/',
                        validator: function(value) {
                            if (value.startsWith("/") === false) {
                                return '{s name=error_message_start_uri}URI must start with a slash{/s}';
                            }
                            return true;
                        },
                        listeners: {
                            afterrender: function() {
                                if (this.getValue() === '') {
                                    this.setValue(this.defaultValue);
                                }
                            }
                        }
                    },
                    targetUrl: {
                        allowBlank: false,
                        fieldLabel: '{s name=target_url}Target URI{/s}'
                    },
                    httpCode: {
                        allowBlank: false,
                        fieldLabel: '{s name=http_code}Http Code{/s}',
                        type: "int",
                        xtype: 'combobox',
                        queryMode: 'local',
                        displayField: 'name',
                        valueField: 'id',
                        defaultValue: 302,
                        store: me.getStore(),
                        listeners: {
                            change: function (combobox, newValue) {
                                let record = me.getStore().findRecord('id', newValue, 0, false, false, true);
                            },
                            afterrender: function() {
                                if (this.getValue() === null) {
                                    this.setValue(this.defaultValue);
                                }
                            }
                        },
                        validator: function(value) {
                            if (value !== me.label301 && value !== me.label302) {
                                return '{s name=error_message_http_code}HTTP Code must be 301 or 302{/s}';
                            }
                            return true;
                        },
                    }
                }
            }],
        };
    },
    getStore: function () {
        let me = this;

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


