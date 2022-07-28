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
            template: 'Magenest_AbandonedCart/abtestcampaign/drawChart',
            grid_rule_el: '',
            data_grid: '',
            title_campaign_first: '',
            title_campaign_second: '',
            total_send_first: '',
            total_send_second: '',
            notice_collection: '',
            selected_rule: [],
            dataDraw: [],
            show_notice:'none'
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
            this.observe([
                "title_campaign_first",
                "title_campaign_second",
                "total_send_first",
                "total_send_second",
                "notice_collection",
                "show_notice"
            ]);
            return this;
        },

        getDataGrid: function () {
            var self = this;
            if (self.data_grid === '') {
                self.data_grid = window.data_grid ?  window.data_grid : '';
            }
        },

        /**
         * get rule selected in grid collection rule
         */
        getSelectRule: function () {
            var self = this;

            /** get data of grid collection rule */
            self.getDataGrid();

            /** init data grid collect rule */
            if (self.grid_rule_el === '') {
                self.grid_rule_el = $('#collection_rule_grid_table');
            }

            /** clean array selected_rule before collection */
            self.selected_rule.length = 0;

            /** collection select rule */
            self.grid_rule_el.find('tbody').find('input[type="checkbox"]').each(function (index) {
                if ($(this).is(":checked")) {
                    self.selected_rule.push(index);
                }
            });

            /** show total */
            self.showTotal();
        },

        /**
         * show name rule and total email send successful
         * selected_rule[0]: id first rule for compare
         * selected_rule[1]: id second rule for compare
         */
        showTotal: function () {
            var self = this;

            /** check number rule select of customer */
            if (self.selected_rule.length < 2 || self.selected_rule.length > 2) {
                this.notice_collection($.mage.__('Please select two rule for compare!'));
                this.show_notice("block");
            } else {
                this.notice_collection('');
                this.show_notice("none");

                /** set title name campaign */
                self.title_campaign_first(self.data_grid[self.selected_rule[0]].name+ ': ');
                self.title_campaign_second(self.data_grid[self.selected_rule[1]].name+ ': ');

                /** set total email send successful */
                self.total_send_first(self.data_grid[self.selected_rule[0]].sent + $.mage.__(' Email'));
                self.total_send_second(self.data_grid[self.selected_rule[1]].sent + $.mage.__(' Email'));

                /** check total email send successful */
                if (parseInt(self.data_grid[self.selected_rule[0]].sent) === 0 || parseInt(self.data_grid[self.selected_rule[1]].sent) === 0) {
                    this.notice_collection($.mage.__('The chart couldn\'t be drawn because no email was sent successfully!'));
                    this.show_notice("block");
                } else {
                    /** calc new data */
                    self.calcBeforeDraw();
                }
            }
        },

        /**
         * init google chart and call function draw
         */
        drawChartColumn: function () {
            var self = this;
            google.charts.load('current', {'packages': ['bar']});
            google.charts.setOnLoadCallback(function () {
                self.drawChart(self)
            });
        },

        /**
         * calc data for draw chart
         */
        calcBeforeDraw: function () {
            var self = this,
                recovered_first_rule, recovered_second_rule;

            /** clean array dataDraw before calc new data */
            self.dataDraw.length = 0;

            var dataAjax = {
                rule_first : self.data_grid[self.selected_rule[0]].id,
                from_first : self.data_grid[self.selected_rule[0]].from_date,
                to_first : self.data_grid[self.selected_rule[0]].to_date,
                rule_second : self.data_grid[self.selected_rule[1]].id,
                from_second : self.data_grid[self.selected_rule[1]].from_date,
                to_second : self.data_grid[self.selected_rule[1]].to_date,
            };

            $.ajax({
                type: "POST",
                url: self.urlAjax,
                data: dataAjax,
                success: function (data) {
                    if(data.message === 'done'){
                        recovered_first_rule = data.first_rule;
                        recovered_second_rule = data.second_rule;
                        /** calc percent open mail, click mail, recovered carts and recovered products each campaign */
                        self.selected_rule.each(function (value,index) {
                            var total_email = self.data_grid[value].sent,
                                opened_email = self.data_grid[value].opened,
                                click_email= self.data_grid[value].clicks,
                                recovered_cart = 0,recovered_product = 0;
                            if(index === 0){
                                recovered_cart = recovered_first_rule.recovered_cart;
                                recovered_product = recovered_first_rule.recovered_product;
                            }else{
                                recovered_cart = recovered_second_rule.recovered_cart;
                                recovered_product = recovered_second_rule.recovered_product;
                            }
                            self.dataDraw.push({
                                    opened_campaign: isNaN(parseInt(opened_email)) ? 0 : parseInt(opened_email) / parseInt(total_email),
                                    clicks_campaign: isNaN(parseInt(click_email)) ? 0 : parseInt(click_email) / parseInt(opened_email),
                                    recovered_cart: isNaN(parseInt(recovered_cart)) ? 0 : parseInt(recovered_cart),
                                    recovered_product: isNaN(parseInt(recovered_product)) ? 0 : parseInt(recovered_product)
                                }
                            );

                            /** draw chart */
                            self.drawChartColumn();
                        });
                    }
                },
                error:function (e) {
                    self.notice_collection($.mage.__(e));
                }
            });
        },

        /**
         * draw bar chart
         */
        drawChart: function (self) {

            // var self = this;

            /** check array dataDraw */
            if (self.dataDraw.length !== 0) {

                /** set data for bar chart*/
                var dataColumnPercent = new google.visualization.DataTable();
                dataColumnPercent.addColumn('string', '');
                dataColumnPercent.addColumn('number', 'Rule 1');
                dataColumnPercent.addColumn('number', 'Rule 2');
                dataColumnPercent.addRows([
                    ['Opened Mails', self.dataDraw[0].opened_campaign, self.dataDraw[1].opened_campaign],
                    ['Clicked Mails', self.dataDraw[0].clicks_campaign, self.dataDraw[1].clicks_campaign]
                ]);

                var dataColumnNumber = new google.visualization.DataTable();
                dataColumnNumber.addColumn('string', '');
                dataColumnNumber.addColumn('number', 'Rule 1');
                dataColumnNumber.addColumn('number', 'Rule 2');
                dataColumnNumber.addRows([
                    ['Recovered Carts', self.dataDraw[0].recovered_cart, self.dataDraw[1].recovered_cart],
                    ['Recovered Products', self.dataDraw[0].recovered_product, self.dataDraw[1].recovered_product],
                ]);

                /** set option for display of bar chart*/
                var optionsChartPercent = {
                    chart: {
                        title: 'Compare Opened & Clicked Mails',
                        subtitle: 'Compare the effectiveness of the campaign',
                    },
                    vAxis: {
                        viewWindowMode: 'explicit',
                        viewWindow: {
                            max: 1,
                            min: 0
                        },
                        format: '# %'
                    },
                    bars: 'vertical',
                    width: 800,
                    height: 600
                };

                var optionsChartNumber = {
                    chart: {
                        title: 'Compare Recovered Carts & Recovered Products',
                        subtitle: 'Compare the effectiveness of the campaign',
                    },
                    vAxis: {
                        viewWindowMode: 'explicit',
                    },
                    bars: 'vertical',
                    width: 800,
                    height: 600
                };

                /** draw bar chart with date and option*/
                var chartPercent = new google.charts.Bar(document.getElementById('columnchart_percent_material_compare'));
                chartPercent.draw(dataColumnPercent, google.charts.Bar.convertOptions(optionsChartPercent));

                var chartNumber = new google.charts.Bar(document.getElementById('columnchart_number_material_compare'));
                chartNumber.draw(dataColumnNumber, google.charts.Bar.convertOptions(optionsChartNumber));
            }
        }
    })
});
