(function ($) {

    list = {

        display: function (data) {

            $("tbody").on("click", ".toggle-row", function (e) {
                e.preventDefault();
                $(this).closest("tr").toggleClass("is-expanded")
            });

            data = $.extend({
                _ajax_custom_list_nonce: $('#_ajax_custom_list_nonce').val(),
                action: '_ajax_fetch_ts_history',
                filter: 'all'
            }, data);

            list.init(data);

        },

        init: function (data) {

            var timer;
            var delay = 500;
            var origin_data = data;

            $('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function (e) {
                e.preventDefault();
                var query = this.search.substring(1);

                var data = $.extend(origin_data, {
                    paged: list.__query(query, 'paged') || '1',
                });

                console.log(data);

                list.update(data);
            });

            $('input[name=paged]').on('keyup', function (e) {

                if (13 == e.which)
                    e.preventDefault();

                var data = $.extend(origin_data, {
                    paged: parseInt($('input[name=paged]').val()) || '1',
                });

                console.log(data);

                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    list.update(data);
                }, delay);
            });

            $('#email-sent-list').on('submit', function (e) {

                e.preventDefault();

            });

            $("tbody tr").prop("title", "");

            $("tbody:not(.table-loading) tr:not(.no-items)").tooltip({
                content: function () {

                    subject_comp = $(this).find(".column-subject_comp").text();
                    error = $(this).find(".column-error").text()

                    tooltip = '<p><strong>' + ts.i18n['subject'] + '</strong>: ' + subject_comp + '</p>';

                    if (error.length > 0)

                        tooltip += '<p><strong style="color: #ff0000;">' + ts.i18n['description_error'] + '</strong>: ' + error + '</p>';

                    return tooltip;

                },
                track: true
            });

            $(".ts-loading").hide();
            $(".history-step").show();

        },

        update: function (data) {

            $("#the-list").addClass("table-loading");
            $(".ts-history-table-loading").show();

            $("#ts-history-table").find("thead").removeClass().addClass(data.filter);

            $.ajax({

                url: ajaxurl,
                data: $.extend(
                    {
                        _ajax_custom_list_nonce: $('#_ajax_custom_list_nonce').val(),
                        action: '_ajax_fetch_ts_history'
                    },
                    data
                ),
                success: function (response) {

                    var response = $.parseJSON(response);

                    if (response.rows.length)
                        $('#the-list').html(response.rows);
                    if (response.column_headers.length)
                        $('thead tr, tfoot tr').html(response.column_headers);
                    if (response.pagination.bottom.length)
                        $('.tablenav.top .tablenav-pages').html($(response.pagination.top).html());
                    if (response.pagination.top.length)
                        $('.tablenav.bottom .tablenav-pages').html($(response.pagination.bottom).html());


                    $("#the-list").removeClass("table-loading");
                    $(".ts-history-table-loading").hide();

                    list.init(data);
                }
            });
        },

        __query: function (query, variable) {

            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable)
                    return pair[1];
            }
            return false;
        }
    }

    $(function () {

        $(".other-infos-toggle a").on("click", function (evt) {
            evt.preventDefault();

            if ($(this).find("span").hasClass("icon-arrow-down")) {
                $("#turbo-stat-chart").hide();
                $(this).find("span").removeClass("icon-arrow-down").addClass("icon-arrow-left");
            }
            else {
                $("#turbo-stat-chart").show();
                $(this).find("span").removeClass("icon-arrow-left").addClass("icon-arrow-down");
            }

        });

        var from_date = $("input[name='from_date']");

        $(from_date).daterangepicker({
            presetRanges: [
                {
                    text: ts.i18n.drp_preset['today'], dateStart: function () {
                    return moment()
                }, dateEnd: function () {
                    return moment()
                }
                },
                {
                    text: ts.i18n.drp_preset['yesterday'], dateStart: function () {
                    return moment().subtract('days', 1)
                }, dateEnd: function () {
                    return moment().subtract('days', 1)
                }
                },
                {
                    text: ts.i18n.drp_preset['lastweek'], dateStart: function () {
                    return moment().subtract('days', 6)
                }, dateEnd: function () {
                    return moment()
                }
                },
                {
                    text: ts.i18n.drp_preset['lastmonth'], dateStart: function () {
                    return moment().startOf('month')
                }, dateEnd: function () {
                    return moment()
                }
                },
                {
                    text: ts.i18n.drp_preset['prevmonth'], dateStart: function () {
                    return moment().subtract('month', 1).startOf('month')
                }, dateEnd: function () {
                    return moment().subtract('month', 1).endOf('month')
                }
                },
                {
                    text: ts.i18n.drp_preset['thisyear'], dateStart: function () {
                    return moment().startOf('year')
                }, dateEnd: function () {
                    return moment()
                }
                },
                {
                    text: ts.i18n.drp_preset['prevyear'], dateStart: function () {
                    return moment().subtract('year', 1).startOf('day')
                }, dateEnd: function () {
                    return moment()
                }
                }
            ],
            applyButtonText: ts.i18n.drp_preset['apply'],
            clearButtonText: ts.i18n.drp_preset['clear'],
            cancelButtonText: ts.i18n.drp_preset['cancel']
        });
        $(from_date).daterangepicker("setRange", {
            start: moment().subtract('days', 7).startOf('day').toDate(),
            end: moment().startOf('day').toDate()
        }).on("change", function () {
            GetChartData(1);
        });

        function respondCanvas() {

            if (window.chart != null)
                window.chart.destroy();

            var c = $('#turbo-stat-chart');
            var ctx = c.get(0).getContext("2d");
            var container = c.parent();

            var $container = $(container);

            c.attr('width', $container.width()); //max width

            c.attr('height', $container.height() / 1.5); //max height

            //Call a function to redraw other content (texts, images etc)

            window.chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    legend: {
                        position: 'bottom'
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10
                            }
                        }]
                    }
                }
            });

        }

        function getType(key) {

            return ts.i18n[key];

        }

        function getColor(key, value) {

            switch (key) {
                case "queued":
                    return "rgba(216,216,216," + value + ")";
                case "delivered":
                    return "rgba(102,102,102," + value + ")";
                case "bounce":
                    return "rgba(255,168,0," + value + ")";
                case "opens":
                    return "rgba(117,197,74," + value + ")";
                case "clicks":
                    return "rgba(30,166,37," + value + ")";
                case "unsubscribes":
                    return "rgba(103,2,2," + value + ")";
                case "spam":
                    return "rgba(255,0,0," + value + ")";
                default:
                    return "rgba(5,141,199," + value + ")";

            }

        }

        var fillBox = function (key, values, tot, all) {

            var total = 0;
            var pct = 0;

            for (i = 0; i < values.length; i++)
                total += values[i];

            pct = Number((total / tot) * 100).toFixed(2);

            pct_part = pct.toString().split('.');

            if (all) {
                $("." + key).find("h4").html(pct_part[0] + ".<span>" + pct_part[1] + "%</span>");
                $("." + key).find("p").text(total);
            }

            else
                $("." + key).find("p").text(pct + "%");


        }

        var GetChartData = function (update) {

            $(".other-infos").hide();
            $(".other-infos-columns").hide();
            $(".other-infos-loading").show();
            $(".other-infos-noresults").hide();
            $(".ts-loading").show();
            $(".history-step").hide();

            $.ajax({
                url: ts.chart_ajax_url,
                method: 'POST',
                dataType: 'json',
                data: {
                    'start_date': moment($(from_date).daterangepicker("getRange").start).format("YYYY-MM-DD"),
                    'end_date': moment($(from_date).daterangepicker("getRange").end).format("YYYY-MM-DD")
                },
                success: function (d) {

                    if (d.data.length == 0) {
                        $(".other-infos-noresults").show();
                        return;
                    }

                    $(".total-email").find("h4").text(d.total);
                    $(".chartjs-hidden-iframe").remove();

                    var ts_datasets = [];

                    $.each(d.data, function (key, values) {

                        ts_datasets.push({
                            label: getType(key),
                            backgroundColor: getColor(key, 0.2),
                            borderColor: getColor(key, 1),
                            pointBorderColor: getColor(key, 1),
                            pointBackgroundColor: "#000000",
                            data: d.data[key],
                            lineTension: 0
                        });

                        if (key == "delivered" || key == "opens" || key == "clicks")
                            fillBox(key, values, d.total, true);

                        else if (key != "all")
                            fillBox(key, values, d.total, false);

                    });

                    $(".other-infos").show();
                    $(".other-infos-columns").css("display", "flex");
                    $(".other-infos-loading").hide();

                    chartData = {
                        labels: d.axis,
                        datasets: ts_datasets
                    };

                    max = Math.max.apply(Math, d.data.all);
                    steps = 10;

                    respondCanvas();

                    if (update == 0) {

                        list.display({
                            'begin': moment($(from_date).daterangepicker("getRange").start).format("YYYY-MM-DD"),
                            'end': moment($(from_date).daterangepicker("getRange").end).format("YYYY-MM-DD")
                        });

                    }

                    else {
                        list.update({
                            'filter': 'all',
                            'begin': moment($(from_date).daterangepicker("getRange").start).format("YYYY-MM-DD"),
                            'end': moment($(from_date).daterangepicker("getRange").end).format("YYYY-MM-DD")
                        });
                    }

                }
            });
        };

        $("div[data-ts-filter]").on("click", function () {
            $("div[data-ts-filter]").removeClass("active");
            $(this).addClass("active");
            list.update({
                'filter': $(this).data("ts-filter"),
                'begin': moment($(from_date).daterangepicker("getRange").start).format("YYYY-MM-DD"),
                'end': moment($(from_date).daterangepicker("getRange").end).format("YYYY-MM-DD")
            });
        });

        GetChartData(0);

    });

})(jQuery);