// {namespace name="backend/scop_redirecter/view/list/redirecter"}
// {block name="backend/scop_redirecter/view/list/redirecter"}
Ext.define('Shopware.apps.ScopRedirecter.view.list.Redirecter', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.scope-redirecter-listing-grid',
    region: 'center',

    configure: function() {
        return {
            detailWindow: 'Shopware.apps.ScopRedirecter.view.detail.Window',
            columns: {
                startUrl: {
                    header: '{s name=start_url}Start URI{/s}'
                },
                targetUrl: {
                    header: '{s name=target_url}Target URI{/s}'
                },
                httpCode: {
                    header: '{s name=http_code}Http Code{/s}'
                }
            },
        };
    },

    createToolbarItems: function () {
        let me = this, items = [];
        me.fireEvent(me.eventAlias + '-before-create-toolbar-items', me, items);

        if (me.getConfig('addButton')) {
            items.push(me.createAddButton());
        }
        if (me.getConfig('deleteButton')) {
            items.push(me.createDeleteButton())
        }

        items.push(me.createExportButton())
        items.push(me.createImportButton())
        me.fireEvent(me.eventAlias + '-before-create-right-toolbar-items', me, items);

        if (me.getConfig('searchField')) {
            items.push('->');
            items.push(me.createSearchField());
        }
        return items;
    },

    createImportButton: function() {
        return Ext.create('Ext.form.Panel', {
            formItemCls: "",
            border: 0,
            padding: 0,
            height: "28px",
            items: [{
                id: 'importBtn',
                xtype: 'filefield',
                name: 'importCsv',
                fieldLabel: '',
                buttonOnly: true,
                labelWidth: 50,
                height: "28px",
                padding: 0,
                margin: 0,
                msgTarget: 'side',
                buttonText: '{s name=import}Import{/s}',
                listeners: {
                    afterrender: function() {
                        var el1 = document.getElementById("importBtn-bodyEl");
                        el1.style.backgroundColor = '0';
                        el1.style.borderLeft = '0';
                        el1.style.background = '0';
                        var el2 = document.getElementById("importBtn-browseButtonWrap");
                        el2.style.backgroundColor = '0';
                        el2.style.borderLeft = '0';
                        el2.style.background = '0';

                    },
                    change: function(){
                        var form = this.up('form').getForm();
                        if(form.isValid()){
                            form.submit({
                                url: '{url module=backend controller=ScopRedirecter action=import}',
                                success: function(fp, o) {
                                    Ext.Msg.alert(
                                        '{s name=success_title}Import completed{/s}',
                                        '{s name=success_msg}The CSV has successfully been imported.' +
                                        'Please refresh your list of redirects{/s}');
                                    var store = Ext.data.StoreManager.get("Shopware.apps.ScopRedirecter.store.Redirecter");
                                    if (!store) {
                                        store.load();
                                    }
                                },
                                failure: function(fp, o) {
                                    Ext.Msg.alert(
                                        '{s name=failure_title}Import failed{/s}',
                                        '{s name=failure_msg}Import failed{/s}');
                                }
                            });
                        }
                    }
                }
            }],
        })
    },

    createExportButton: function() {
        return Ext.create('Ext.form.Panel', {
            border: 0,
            items: [{
                xtype: 'button',
                name: 'exportCsv',
                fieldLabel: '',
                buttonOnly: true,
                labelWidth: 50,
                msgTarget: 'side',
                border: 0,
                text: "Export",
                handler: function(){
                    window.open(window.location.href + 'ScopRedirecter/export/redirects.csv')
                    var form = this.up('form').getForm();
                    if(form.isValid()) {
                        form.submit({
                            url: '{url module=backend controller=ScopRedirecter action=export}',

                        });
                    }
                }
            }],
        })
    },
});
// {/block}
