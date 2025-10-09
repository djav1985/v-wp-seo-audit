function dynamicThumbnail(url) {
    if (!url || typeof url !== 'object') {
        url = {};
    }
    jQuery.each(url, function(key, data) {
        var onReady = function(img, downloadUrl) {
            img.attr("src", downloadUrl);
            img.on("error", function() {
                onError(img);
            });
        };
        var onError = function(img) {
            img.attr("src", _global.baseUrl + "/img/not-available.png");
        };
        var image = jQuery('#thumb_'+key);
        // Use the thumbnail URL directly since we're now caching on the server
        if (data && data.thumb) {
            onReady(image, data.thumb);
        } else {
            onError(image);
        }
    });
}

jQuery(function($){
    $("a.disabled, li.disabled a").click(function(){
        return false;
    });
});

var WrHelper = (function () {
    return {
        isSameArray: function (a1, a2) {
            var copy = a2.slice(),
                i = 0,
                l = a1.length
            ;
            for(; i < l; i++) {
                var index = copy.indexOf(a1[i]);
                if(index === -1) {
                    return false;
                } else {
                    copy.splice(index, 1);
                }
            }
            return copy.length === 0;
        },
    }
})();

(function($) {
    'use strict';

    function getGlobalSetting(key, fallback) {
        if (typeof _global !== 'undefined' && _global && Object.prototype.hasOwnProperty.call(_global, key)) {
            return _global[key];
        }
        return fallback;
    }

    function getAjaxUrl() {
        return getGlobalSetting('ajaxUrl', '/wp-admin/admin-ajax.php');
    }

    function getNonce() {
        return getGlobalSetting('nonce', '');
    }

    function normalizeElement(element, fallbackSelector) {
        if (element && element.jquery) {
            return element;
        }
        if (element) {
            return $(element);
        }
        if (fallbackSelector) {
            return $(fallbackSelector);
        }
        return $();
    }

    function resolveContainer($context) {
        var $container = $context && $context.length ? $context.closest('.v-wp-seo-audit-container') : $();
        if (!$container.length) {
            $container = $('.v-wp-seo-audit-container').first();
        }
        return $container;
    }

    window.vWpSeoAudit = window.vWpSeoAudit || {};

    window.vWpSeoAudit.generateReport = function(domain, options) {
        var settings = $.extend({
            ajaxUrl: getAjaxUrl(),
            nonce: getNonce(),
            $container: null,
            $errors: null,
            $progressBar: null,
            scrollTo: true,
            manageProgressBar: true,
            beforeSend: null,
            afterSend: null
        }, options || {});

        var $container = normalizeElement(settings.$container, '.v-wp-seo-audit-container').first();
        var $errors = normalizeElement(settings.$errors, '#errors');
        var $progressBar = normalizeElement(settings.$progressBar, '#progress-bar');
        var manageProgress = settings.manageProgressBar !== false;

        settings.$container = $container;

        if ($errors.length) {
            $errors.hide().html('');
        }

        if (typeof settings.beforeSend === 'function') {
            settings.beforeSend();
        }

        if (manageProgress && $progressBar.length) {
            $progressBar.show();
        }

        var request = $.ajax({
            url: settings.ajaxUrl,
            type: 'POST',
            data: {
                action: 'v_wp_seo_audit_generate_report',
                domain: domain,
                nonce: settings.nonce
            },
            dataType: 'json'
        });

        var finalize = function() {
            if (manageProgress && $progressBar.length) {
                $progressBar.hide();
            }
            if (typeof settings.afterSend === 'function') {
                settings.afterSend();
            }
        };

        request.done(function(response) {
            finalize();

            if (response && response.success) {
                var html = response.data && response.data.html ? response.data.html : '';
                var $targetContainer = $container;

                if ($targetContainer.length) {
                    $targetContainer.html(html);
                } else {
                    var $form = $('#website-form');
                    if ($form.length) {
                        var $parent = $form.parent();
                        $targetContainer = $('<div class="v-wp-seo-audit-container"></div>').html(html);
                        $parent.html($targetContainer);
                        settings.$container = $targetContainer;
                    }
                }

                if (settings.scrollTo !== false && $targetContainer.length) {
                    $('html, body').animate({
                        scrollTop: $targetContainer.offset().top - 100
                    }, 500);
                }
            } else {
                var message = response && response.data && response.data.message ? response.data.message : 'Failed to generate report';
                if ($errors.length) {
                    $errors.html(message).show();
                } else {
                    window.alert(message);
                }
            }
        });

        request.fail(function() {
            finalize();

            if ($errors.length) {
                $errors.html('An error occurred while generating the report. Please try again.').show();
            } else {
                window.alert('An error occurred while generating the report. Please try again.');
            }
        });

        return request;
    };

    $(function() {
        var $domainInput = $('#domain');
        var $submit = $('#submit');
        var $errors = $('#errors');
        var $progressBar = $('#progress-bar');

        $('#submit').on('click', function(e) {
            e.preventDefault();

            var domain = $domainInput.val().trim();

            if ($errors.length) {
                $errors.hide().html('');
            }

            if (!domain) {
                if ($errors.length) {
                    $errors.html('Please enter a domain name').show();
                } else {
                    window.alert('Please enter a domain name');
                }
                return;
            }

            domain = domain.replace(/^(https?:\/\/)?(www\.)?/i, '');
            domain = domain.replace(/\/$/, '');

            var domainPattern = /^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i;
            if (!domainPattern.test(domain)) {
                if ($errors.length) {
                    $errors.html('Please enter a valid domain name').show();
                } else {
                    window.alert('Please enter a valid domain name');
                }
                return;
            }

            $domainInput.val(domain);

            if ($progressBar.length) {
                $progressBar.show();
            }
            $submit.prop('disabled', true);

            var ajaxUrl = getAjaxUrl();
            var nonce = getNonce();

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'v_wp_seo_audit_validate',
                    domain: domain,
                    nonce: nonce
                },
                dataType: 'json'
            }).done(function(response) {
                if (response && response.success && response.data && response.data.domain) {
                    window.vWpSeoAudit.generateReport(response.data.domain, {
                        ajaxUrl: ajaxUrl,
                        nonce: nonce,
                        $container: resolveContainer($submit),
                        $errors: $errors,
                        $progressBar: $progressBar,
                        manageProgressBar: false,
                        afterSend: function() {
                            if ($progressBar.length) {
                                $progressBar.hide();
                            }
                            $submit.prop('disabled', false);
                        }
                    });
                } else {
                    var message = response && response.data && response.data.message ? response.data.message : 'Validation failed';
                    if ($errors.length) {
                        $errors.html(message).show();
                    } else {
                        window.alert(message);
                    }
                    if ($progressBar.length) {
                        $progressBar.hide();
                    }
                    $submit.prop('disabled', false);
                }
            }).fail(function() {
                if ($errors.length) {
                    $errors.html('An error occurred during validation. Please try again.').show();
                } else {
                    window.alert('An error occurred during validation. Please try again.');
                }
                if ($progressBar.length) {
                    $progressBar.hide();
                }
                $submit.prop('disabled', false);
            });
        });

        $domainInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#submit').trigger('click');
            }
        });

        $('body').on('click', '.v-wp-seo-audit-view-report', function(e) {
            e.preventDefault();

            var $trigger = $(this);
            var domain = $trigger.data('domain');

            if (!domain) {
                var fallback = $trigger.attr('href');
                if (fallback) {
                    window.location.href = fallback;
                }
                return;
            }

            window.vWpSeoAudit.generateReport(domain, {
                $container: resolveContainer($trigger),
                beforeSend: function() {
                    $trigger.addClass('disabled').attr('aria-busy', 'true');
                },
                afterSend: function() {
                    $trigger.removeClass('disabled').removeAttr('aria-busy');
                }
            });
        });

        $('body').on('click', '.v-wp-seo-audit-download-pdf', function(e) {
            e.preventDefault();

            var $trigger = $(this);
            var domain = $trigger.data('domain');

            if (!domain) {
                window.alert('Domain is required to download PDF');
                return;
            }

            // Show loading state
            var originalText = $trigger.text();
            $trigger.addClass('disabled').attr('aria-busy', 'true').text('Generating PDF...');

            var ajaxUrl = getAjaxUrl();
            var nonce = getNonce();
            
            // Debug: Check if nonce is available
            if (!nonce) {
                console.error('V-WP-SEO-Audit: Nonce is not available');
                window.alert('Error: Security token is not available. Please refresh the page and try again.');
                $trigger.removeClass('disabled').removeAttr('aria-busy').text(originalText);
                return;
            }

            // Create a form and submit it to trigger file download
            var $form = $('<form>', {
                method: 'POST',
                action: ajaxUrl,
                target: '_blank'
            });

            $form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'v_wp_seo_audit_download_pdf'
            }));

            $form.append($('<input>', {
                type: 'hidden',
                name: 'domain',
                value: domain
            }));

            $form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: nonce
            }));

            // Append form to body, submit it, and remove it
            $form.appendTo('body').submit();

            // Clean up form after a short delay
            setTimeout(function() {
                $form.remove();
            }, 100);

            // Restore button state after a delay
            setTimeout(function() {
                $trigger.removeClass('disabled').removeAttr('aria-busy').text(originalText);
            }, 2000);
        });
    });
})(jQuery);

var WrPsi = (function () {
    var baseApiUrl = 'https://googlechrome.github.io/lighthouse/viewer/';

    return function (o) {
        var options = jQuery.extend({}, {
            'i18nEnterFullscreen': 'Enter fullscreen mode',
            'i18nExitFullscreen': 'Exit fullscreen mode',
            'iframeWrapperSelector':  '.psi__iframe-wrapper',
            'analyzeBtnSelector': '.psi__analyze-btn',
            'url': '',
            'locale': 'en',
            'runInstantly': false,
            'categorySelector': '[data-psi-category]', // must be checkbox
            'strategySelector': '[name="psi__strategy"]', // must be radio
        }, o);

        var category$ = jQuery(options.categorySelector);
        var strategy$ = jQuery(options.strategySelector);
        var iframeWrapper$ = jQuery(options.iframeWrapperSelector);
        var btnAnalyze$ = jQuery(options.analyzeBtnSelector);

        var currentCategories = [];
        var currentStrategy = null;
        var isFullscreen = false;

        btnAnalyze$.on("click", function (e) {
            e.preventDefault();
            analyze();
        });

        if(options.runInstantly) {
            analyze();
        }

        return {
            destroy: function () {
                isFullscreen = false;
                destroy(iframeWrapper$);
            }
        };

        //
        // Instance function
        //

        function analyze() {
            var selectedCategories = getCheckedValues(category$);
            var selectedStrategy = getFirstChecked(strategy$);

            if(WrHelper.isSameArray(currentCategories, selectedCategories) && currentStrategy === selectedStrategy)  {
                //console.log(`Skip!`);
                return;
            }

            destroy(iframeWrapper$);

            currentStrategy = selectedStrategy;
            currentCategories = selectedCategories;

            var src = buildIframeSrc({
                strategy: selectedStrategy,
                category: selectedCategories,
                locale: options.locale,
                url: options.url,
            });

            var btnToggleView$ = jQuery("<button>", {
                text: isFullscreen ? options.i18nExitFullscreen : options.i18nEnterFullscreen,
                class: "btn btn-danger psi__btn-view-mode" + (isFullscreen ? " psi__btn-view-mode-fullscreen" : " psi__btn-view-mode-normal"),
            }).on("click", function (e) {
                e.preventDefault();
                if(isFullscreen) {
                    isFullscreen = false;
                    iframeWrapper$.removeClass('psi__fullscreen').addClass('psi__content-view');
                    jQuery(document.body).removeClass('psi__body-fullscreen');
                    jQuery(this).text(options.i18nEnterFullscreen).removeClass('psi__btn-view-mode-fullscreen').addClass('psi__btn-view-mode-normal');
                } else {
                    isFullscreen = true;
                    iframeWrapper$.removeClass('psi__content-view').addClass('psi__fullscreen');
                    jQuery(document.body).addClass('psi__body-fullscreen');
                    jQuery(this).text(options.i18nExitFullscreen).removeClass('psi__btn-view-mode-normal').addClass('psi__btn-view-mode-fullscreen');
                }
            });

            var iframe$ = jQuery('<iframe>', {
                src: src,
            });

            iframeWrapper$.addClass(isFullscreen ? "psi__fullscreen" : "psi__content-view");

            iframeWrapper$.append(
                btnToggleView$,
                iframe$,
            );
        }
    };

    //
    //  Psi Helpers
    //

    function destroy(iframeWrapper$) {
        iframeWrapper$.empty();
    }

    function getCheckedValues(selector$) {
        var selected = [];
        selector$.each(function () {
            if(jQuery(this).is(":checked")) {
                selected.push(jQuery(this).val());
            }
        });
        return selected;
    }

    function getFirstChecked(selector$) {
        var selected = getCheckedValues(selector$);
        return selected.length > 0 ? selected[0] : "";
    }

    function encode(a) {
        return a.map(function (item) {
            return Array.isArray(item) ? encode(item) : encodeURIComponent(item.k)+'='+encodeURIComponent(item.v);
        }).join('&');
    }

    function buildIframeSrc(params) {
        params = params || {};
        params.strategy = params.strategy || 'desktop';
        params.locale = params.locale || 'en';
        params.category = params.category || [];
        params.url = params.url || '';

        return baseApiUrl + '?' + encode([
            {k: "psiurl", v: params.url},
            {k: "strategy", v: params.strategy},
            params.category.map(function (item) {
                return {k: "category", v: item};
            }),
            {k: "locale", v: params.locale}
        ]);
    }
})();


