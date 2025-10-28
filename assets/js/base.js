function dynamicThumbnail(url) {
    if (!url || typeof url !== 'object') {
        url = {};
    }
    jQuery.each(url, function(key, data) {
        var onReady = function(img, downloadUrl) {
            // Only try to load if we have a valid URL
            if (downloadUrl && downloadUrl.length > 0) {
                img.attr("src", downloadUrl);
                img.on("error", function() {
                    onError(img);
                });
            } else {
                onError(img);
            }
        };
        var onError = function(img) {
            var baseUrl = (typeof _global !== 'undefined' && _global.baseUrl) ? _global.baseUrl : '';
            img.attr("src", baseUrl + "/assets/img/not-available.png");
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
        var $container = $context && $context.length ? $context.closest('.v-wpsa-container') : $();
        if (!$container.length) {
            $container = $('.v-wpsa-container').first();
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
            afterSend: null,
            force: false
        }, options || {});

        var $container = normalizeElement(settings.$container, '.v-wpsa-container').first();
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

        var ajaxData = {
            action: 'v_wpsa_generate_report',
            domain: domain,
            nonce: settings.nonce,
            _cache_bust: new Date().getTime()
        };

        if (settings.force) {
            ajaxData.force = '1';
        }

        var request = $.ajax({
            url: settings.ajaxUrl,
            type: 'POST',
            data: ajaxData,
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
                // If the server provided a fresh nonce, update our local nonce variable
                if (response.data && response.data.nonce) {
                    settings.nonce = response.data.nonce;
                    // Also update the global nonce if available
                    if (typeof _global !== 'undefined') {
                        _global.nonce = response.data.nonce;
                    }
                }
                var $targetContainer = $container;

                if ($targetContainer.length) {
                    $targetContainer.html(html);
                    // Store the fresh nonce on the container for later use (e.g., PDF download)
                    if (response.data && response.data.nonce) {
                        $targetContainer.attr('data-nonce', response.data.nonce);
                    }
                } else {
                    var $form = $('#website-form');
                    if ($form.length) {
                        var $parent = $form.parent();
                        $targetContainer = $('<div class="v-wpsa-container"></div>').html(html);
                        // Store the fresh nonce on the container for later use (e.g., PDF download)
                        if (response.data && response.data.nonce) {
                            $targetContainer.attr('data-nonce', response.data.nonce);
                        }
                        $parent.html($targetContainer);
                        settings.$container = $targetContainer;
                    }
                }

                // Update URL hash for deep linking (replace dots with dashes)
                if (domain && window.history && window.history.replaceState) {
                    var hashDomain = domain.replace(/\./g, '-');
                    window.history.replaceState(null, null, '#' + hashDomain);
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
        // Check for hash-based deep linking on page load. Prefer cached loader to avoid re-analysis.
        function checkHashAndLoadReport() {
            var hash = window.location.hash;
            if (hash && hash.length > 1) {
                // Remove the # and convert dashes back to dots
                var domain = hash.substring(1).replace(/-/g, '.');
                if (domain) {
                    // Prefer cached loader if available to avoid regenerating the report.
                    if (window.vWpSeoAudit && typeof window.vWpSeoAudit.loadCachedReport === 'function') {
                        window.vWpSeoAudit.loadCachedReport(domain, {
                            afterSend: function() {
                                var $target = $('.v-wpsa-container').first();
                                if ($target.length) {
                                    $('html, body').animate({
                                        scrollTop: $target.offset().top - 100
                                    }, 500);
                                }
                            }
                        });
                    } else {
                        // Fallback to generateReport which may re-run analysis if needed.
                        window.vWpSeoAudit.generateReport(domain, {
                            scrollTo: true
                        });
                    }
                }
            }
        }

        // Run on page load
        checkHashAndLoadReport();

        // Also listen for hash changes (e.g., user clicks browser back/forward)
        $(window).on('hashchange', function() {
            checkHashAndLoadReport();
        });

        // Use delegated event handlers so they work with AJAX-loaded content
        // Bind to body so handlers attach to dynamically loaded elements
        $('body').on('click', '#submit', function(e) {
            e.preventDefault();

            var $submit = $(this);
            var $domainInput = $('#domain');
            var $errors = $('#errors');
            var $progressBar = $('#progress-bar');
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

            // If the container has a data-nonce attribute (server-injected), prefer it
            var $container = resolveContainer($submit);
            if ($container && $container.length && $container.data('nonce')) {
                nonce = $container.data('nonce');
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'v_wpsa_validate',
                    domain: domain,
                    nonce: nonce,
                    _cache_bust: new Date().getTime()
                },
                dataType: 'json'
            }).done(function(response) {
                if (response && response.success && response.data && response.data.domain) {
                    // Check if this is a force update request
                    var forceUpdate = $submit.data('force-update') === true;
                    if (forceUpdate) {
                        $submit.removeData('force-update');
                    }

                    window.vWpSeoAudit.generateReport(response.data.domain, {
                        ajaxUrl: ajaxUrl,
                        nonce: nonce,
                        $container: resolveContainer($submit),
                        $errors: $errors,
                        $progressBar: $progressBar,
                        manageProgressBar: false,
                        force: forceUpdate,
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

        // Use delegated event handler for Enter key in domain input
        $('body').on('keypress', '#domain', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#submit').trigger('click');
            }
        });

        // Load cached HTML report (view-only) without triggering re-analysis.
        window.vWpSeoAudit.loadCachedReport = function(domain, options) {
            var settings = $.extend({
                $container: null,
                beforeSend: null,
                afterSend: null
            }, options || {});

            var $container = normalizeElement(settings.$container, '.v-wpsa-container').first();

            if (typeof settings.beforeSend === 'function') {
                settings.beforeSend();
            }

            $.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'v_wpsa_get_cached_report',
                    domain: domain,
                    nonce: getNonce(),
                    _cache_bust: new Date().getTime()
                }
            }).done(function(response) {
                if (response && response.success) {
                    var html = response.data && response.data.html ? response.data.html : '';
                    if ($container.length) {
                        $container.html(html);
                        if (response.data && response.data.nonce) {
                            $container.attr('data-nonce', response.data.nonce);
                        }
                    }
                    if (typeof settings.afterSend === 'function') {
                        settings.afterSend();
                    }
                } else {
                    var message = response && response.data && response.data.message ? response.data.message : 'Failed to load report';
                    window.alert(message);
                    if (typeof settings.afterSend === 'function') {
                        settings.afterSend();
                    }
                }
            }).fail(function() {
                window.alert('An error occurred while loading the report.');
                if (typeof settings.afterSend === 'function') {
                    settings.afterSend();
                }
            });
        };

        // Bind review button to load the cached report rather than regenerating it.
        $('body').on('click', '.v-wpsa-view-report', function(e) {
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

            window.vWpSeoAudit.loadCachedReport(domain, {
                $container: resolveContainer($trigger),
                beforeSend: function() {
                    $trigger.addClass('disabled').attr('aria-busy', 'true');
                },
                afterSend: function() {
                    $trigger.removeClass('disabled').removeAttr('aria-busy');
                }
            });
        });

        $('body').on('click', '.v-wpsa-download-pdf', function(e) {
            e.preventDefault();

            var $trigger = $(this);
            var domain = $trigger.data('domain');

            if (!domain) {
                window.alert('Domain is required to download PDF');
                return;
            }

            // Show loading state
            var originalText = $.trim($trigger.text());
            $trigger.addClass('disabled')
                .attr('aria-busy', 'true')
                .prop('disabled', true)
                .text('Generating PDF...');

            var ajaxUrl = getAjaxUrl();
            var nonce = getNonce();

            // If the container has a data-nonce attribute (server-injected), prefer it
            var $container = resolveContainer($trigger);
            if ($container && $container.length && $container.data('nonce')) {
                nonce = $container.data('nonce');
            }
            
            // Debug: Check if nonce is available
            if (!nonce) {
                console.error('v-wpsa: Nonce is not available');
                window.alert('Error: Security token is not available. Please refresh the page and try again.');
                $trigger.removeClass('disabled')
                    .removeAttr('aria-busy')
                    .prop('disabled', false)
                    .text(originalText);
                return;
            }

            // Use XMLHttpRequest to download PDF as blob
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxUrl, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                $trigger.removeClass('disabled')
                    .removeAttr('aria-busy')
                    .prop('disabled', false)
                    .text(originalText);
                
                if (xhr.status === 200) {
                    var contentType = xhr.getResponseHeader('Content-Type');
                    
                    // Check if response is a PDF
                    if (contentType && contentType.indexOf('application/pdf') !== -1) {
                        // Create a blob URL and trigger download
                        var blob = xhr.response;
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = domain + '.pdf';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    } else {
                        // Response might be JSON error, try to parse it
                        var reader = new FileReader();
                        reader.onload = function() {
                            try {
                                var response = JSON.parse(reader.result);
                                var message = 'Failed to download PDF';
                                if (response && response.data && response.data.message) {
                                    message = response.data.message;
                                }
                                window.alert('Error: ' + message);
                            } catch (e) {
                                window.alert('Error: Failed to download PDF. Please try again.');
                            }
                        };
                        reader.readAsText(xhr.response);
                    }
                } else {
                    window.alert('Error: Failed to download PDF (HTTP ' + xhr.status + '). Please try again.');
                }
            };
            
            xhr.onerror = function() {
                $trigger.removeClass('disabled')
                    .removeAttr('aria-busy')
                    .prop('disabled', false)
                    .text(originalText);
                window.alert('Error: Network error occurred. Please try again.');
            };
            
            // Send the request with form data
            var formData = 'action=v_wpsa_download_pdf&domain=' + encodeURIComponent(domain) + '&nonce=' + encodeURIComponent(nonce);
            xhr.send(formData);
        });

        // Delete report handler (admin only)
        $('body').on('click', '.v-wpsa-delete-report', function(e) {
            e.preventDefault();

            var $trigger = $(this);
            var domain = $trigger.data('domain');

            if (!domain) {
                window.alert('Domain is required to delete report');
                return;
            }

            // Confirm deletion
            if (!window.confirm('Are you sure you want to delete the report for "' + domain + '"? This action cannot be undone.\n\nThis will remove:\n- All database records\n- PDF files\n- Thumbnail images')) {
                return;
            }

            // Show loading state
            var originalText = $trigger.text();
            $trigger.addClass('disabled').prop('disabled', true).text('Deleting...');

            var ajaxUrl = getAjaxUrl();
            var nonce = getNonce();

            // If the container has a data-nonce attribute (server-injected), prefer it
            var $container = resolveContainer($trigger);
            if ($container && $container.length && $container.data('nonce')) {
                nonce = $container.data('nonce');
            }

            // Check if nonce is available
            if (!nonce) {
                console.error('v-wpsa: Nonce is not available');
                window.alert('Error: Security token is not available. Please refresh the page and try again.');
                $trigger.removeClass('disabled').prop('disabled', false).text(originalText);
                return;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'v_wpsa_delete_report',
                    domain: domain,
                    nonce: nonce,
                    _cache_bust: new Date().getTime()
                },
                dataType: 'json'
            }).done(function(response) {
                $trigger.removeClass('disabled').prop('disabled', false).text(originalText);

                if (response && response.success) {
                    window.alert('Report deleted successfully!');
                    // Remove the report container and show the form
                    if ($container.length) {
                        $container.fadeOut(400, function() {
                            // Show the form again
                            $('#update_form').fadeIn();
                            // Scroll to the form
                            $('html, body').animate({
                                scrollTop: $('#update_form').offset().top - 100
                            }, 500);
                        });
                    } else {
                        // Fallback: just reload the page
                        window.location.reload();
                    }
                } else {
                    var message = response && response.data && response.data.message ? response.data.message : 'Failed to delete report';
                    window.alert('Error: ' + message);
                }
            }).fail(function() {
                $trigger.removeClass('disabled').prop('disabled', false).text(originalText);
                window.alert('Error: Network error occurred. Please try again.');
            });
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


