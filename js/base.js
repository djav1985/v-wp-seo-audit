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
        if(_global.proxyImage === 1) {
            var pp = new PagePeekerHelper(image, data, onReady, onError);
            pp.poll();
        } else {
            onReady(image, data.thumb);
        }
    });
}

jQuery(function($){
    $("a.disabled, li.disabled a").click(function(){
        return false;
    });
});

// Constructor
function PagePeekerHelper(image, data, onReady, onError) {
    jQuery.ajaxSetup({ cache: false });
    this.proxy = _global.baseUrl+'/index.php?r=PagePeekerProxy/index';
    this.data = data;
    this.onReady = onReady;
    this.onError = onError;
    this.image = image;
    this.pollTime = 20; // In seconds
    this.execLimit = 3; // If after x requests PP willn't response with status "Ready", then clear interval to avoid ddos attack.
}

PagePeekerHelper.prototype.poll = function() {
    var self = this,
        size = this.data.size || 'm',
        url = this.data.url || '',
        proxyReset = this.proxy + "?" + jQuery.param({
            size: size,
            url: url,
            method: 'reset'
        }),

        proxyPoll = this.proxy + "?" + jQuery.param({
            size: size,
            url: url,
            method: 'poll'
        }),
        limit = this.execLimit,
        i = 0,
        isFirstCall = true;

    // Flush the image
    jQuery.get(proxyReset, function() {
        //console.log("Reseting " + url);

        var pollUntilReady = function(cb) {
            //console.log("Polling " + url + " " + (i + 1) + " times");

            jQuery.getJSON(proxyPoll, function(data) {
                //console.log("Received", data);
                var isReady = (data && data.IsReady) || 0;
                if(isReady) {
                    //console.log("The " + url + " is ready: " + isReady);
                    self.onReady.apply(self, [self.image, self.data.thumb]);
                    return true;
                }
                if(data && data.Error) {
                    self.onError.apply(self, [self.image]);
                    return true;
                }
                cb();
            }).fail(function() {
                //console.log('Failed to request local proxy script. Clearing the timeout');
                self.onError.apply(self, [self.image]);
            });
        };


        (function pollThumbnail() {
            var timeout = isFirstCall ? 0 : self.pollTime * 1000;
            setTimeout(function() {
                pollUntilReady(function() {
                    //console.log("Async " + url + " has done");
                    isFirstCall = false;
                    i++;
                    if(i < limit) {
                        pollThumbnail();
                    } else {
                        //console.log("Reached limit of reuqests for " + url);
                        self.onError.apply(self, [self.image]);
                    }
                });
            }, timeout);
        })();

    }).fail(function() {
        self.onError.apply(self, [self.image]);
    });
};

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

// Form submission handler
jQuery(function($) {
    $('#submit').on('click', function(e) {
        e.preventDefault();
        
        var domain = $('#domain').val().trim();
        var $errors = $('#errors');
        var $progressBar = $('#progress-bar');
        var $container = $('.v-wp-seo-audit-container');
        
        // Hide previous errors
        $errors.hide().html('');
        
        // Validate domain
        if (!domain) {
            $errors.html('Please enter a domain name').show();
            return;
        }
        
        // Basic client-side validation
        // Remove protocol if present
        domain = domain.replace(/^(https?:\/\/)?(www\.)?/i, '');
        domain = domain.replace(/\/$/, ''); // Remove trailing slash
        
        // Simple domain pattern check
        var domainPattern = /^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i;
        if (!domainPattern.test(domain)) {
            $errors.html('Please enter a valid domain name').show();
            return;
        }
        
        // Update the input with cleaned domain
        $('#domain').val(domain);
        
        // Show progress bar
        $progressBar.show();
        $(this).prop('disabled', true);
        
        // Get the AJAX URL from global variable
        var ajaxUrl = (typeof _global !== 'undefined' && _global.ajaxUrl) ? _global.ajaxUrl : '/wp-admin/admin-ajax.php';
        var nonce = (typeof _global !== 'undefined' && _global.nonce) ? _global.nonce : '';
        
        // Step 1: Validate domain via AJAX
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'v_wp_seo_audit_validate',
                domain: domain,
                nonce: nonce
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Domain validated, now generate the report
                    generateReport(response.data.domain);
                } else {
                    // Validation failed, show error
                    var errorMessage = response.data && response.data.message ? response.data.message : 'Validation failed';
                    $errors.html(errorMessage).show();
                    $progressBar.hide();
                    $('#submit').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                $errors.html('An error occurred during validation. Please try again.').show();
                $progressBar.hide();
                $('#submit').prop('disabled', false);
            }
        });
        
        // Function to generate report
        function generateReport(validatedDomain) {
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'v_wp_seo_audit_generate_report',
                    domain: validatedDomain,
                    nonce: nonce
                },
                dataType: 'json',
                success: function(response) {
                    $progressBar.hide();
                    $('#submit').prop('disabled', false);
                    
                    if (response.success) {
                        // Replace the container content with the report
                        if ($container.length) {
                            $container.html(response.data.html);
                        } else {
                            // If container doesn't exist, create it and replace content
                            var $parent = $('#website-form').parent();
                            $parent.html('<div class="v-wp-seo-audit-container">' + response.data.html + '</div>');
                        }
                        
                        // Scroll to top of results
                        $('html, body').animate({
                            scrollTop: $container.offset().top - 100
                        }, 500);
                    } else {
                        var errorMessage = response.data && response.data.message ? response.data.message : 'Failed to generate report';
                        $errors.html(errorMessage).show();
                    }
                },
                error: function(xhr, status, error) {
                    $progressBar.hide();
                    $('#submit').prop('disabled', false);
                    $errors.html('An error occurred while generating the report. Please try again.').show();
                }
            });
        }
    });
    
    // Allow Enter key to submit
    $('#domain').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#submit').trigger('click');
        }
    });
});

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


