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
    this.proxy = _global.baseUrl+'/index.php/proxy';
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


