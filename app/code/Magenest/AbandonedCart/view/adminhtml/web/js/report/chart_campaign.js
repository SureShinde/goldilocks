define([
    "jquery",
    "ko",
    "uiClass",
    "uiComponent",
    'mage/translate'
], function ($, ko, Class, Component) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Magenest_AbandonedCart/report/drawReport',
            data_draw: '',
        },

        initialize: function () {
            this._super();
        },

        /**
         * Initialize observable properties
         */
        initObservable: function () {
            var self = this;
            this._super();
            self.data_draw = self.dataDraw;
            if(self.data_draw.message){
                self.drawChartColumn();
            }
            return this;
        },

        /**
         * init google chart and call function draw
         */
        drawChartColumn: function () {
            var self = this;
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(function () {
                self.drawOpenedRate(self);
                self.drawClickRate(self);
                self.drawRestoreRate(self);
                self.drawProductRate(self);
            });
        },

        /**
         * data for pie chart opened email rate
         */
        drawOpenedRate: function (self) {
            var element = document.getElementById('columnchart_opened_material_report'),
                title = 'Open Rate',
                name_item = {
                  first : "Opened",
                  second: 'Unopened'
                },
                data = {
                    first : self.data_draw.total_opened,
                    second: self.data_draw.total_unopened
                };
            self.drawChart(element,title,name_item,data);
        },


        /**
         * data for pie chart clicked rate
         */
        drawClickRate: function (self) {
            var element = document.getElementById('columnchart_clicks_material_report'),
                title = 'Click Rate',
                name_item = {
                    first : "Clicked",
                    second: 'Unclicked'
                },
                data = {
                    first : self.data_draw.total_clicks,
                    second: self.data_draw.total_unclicked
                };
            self.drawChart(element,title,name_item,data);
        },

        /**
         * data for pie chart restore rate
         */
        drawRestoreRate: function (self) {
            var element = document.getElementById('columnchart_restore_material_report'),
                title = 'Recovered Cart Rate',
                name_item = {
                    first : "Recovered",
                    second: 'Unrecovered'
                },
                data = {
                    first : self.data_draw.total_recovered_cart,
                    second: self.data_draw.total_unrecovered_cart
                };
            self.drawChart(element,title,name_item,data);
        },

        /**
         *  data for pie chart product rate
         */
        drawProductRate: function (self) {

            var element = document.getElementById('columnchart_products_material_report'),
                title = 'Recovered Product Rate',
                name_item = {
                    first : "Recovered",
                    second: 'Unrecovered'
                },
                data = {
                    first : self.data_draw.total_recovered_product,
                    second: self.data_draw.total_unrecovered_product
                };
            self.drawChart(element,title,name_item,data);
        },

        /**
         * Draw chart
         * @param element
         * @param title_chart
         * @param name_item
         * @param data
         */
        drawChart: function (element,title_chart,name_item,data) {
            var dataChart = new google.visualization.DataTable();
            dataChart.addColumn('string', 'Type');
            dataChart.addColumn('number', 'Value');
            dataChart.addRows([
                [name_item.first,data.first],
                [name_item.second,data.second]
            ]);
                var options = {
                'legend': {
                    position: 'bottom',
                    textStyle: {color: 'black', fontSize: 14}
                },
                is3D: true,
                'title': title_chart,
                titleTextStyle: {
                    fontSize: 15,
                    bold: false
                },
                pieSliceTextStyle: {
                    fontSize: 15,
                    color: '#41362f',
                    bold: true,
                    italic: true
                },
                'width': window.innerWidth / 5,
                'height': 400,
                tooltip: {
                    textStyle: {color: 'black'},
                    showColorCode: true
                },
                slices: {
                    0: {color: '#ffcb4d'},
                    1: {color: '#f49c5b'}
                }
            },
            chart = new google.visualization.PieChart(element);
            chart.draw(dataChart, options);
        }
    })
});
