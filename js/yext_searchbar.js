(function ($, Drupal, drupalSettings) {

    const api = 'https://liveapi-cached.yext.com/v2/accounts/me/';
    const endpointAll = 'answers/autocomplete';
    const endpointVertical = 'answers/vertical/autocomplete';

    // for animated placeholder text within the searchbar
    const typedOptions = {
        showCursor: true,
        cursorChar: "|",
        typeSpeed: 45,
        backSpeed: 20,
        smartBackspace: true,
        loop: true,
        startDelay: 500,
        backDelay: 2000,
        attr: "placeholder",
    };

    // for retrieving strings to animate as placeholder text
    const autocompleteParams = {
        v: (new Date()).toISOString().slice(0,10).replace(/-/g,''),
        api_key: drupalSettings.yext.yextOptions.apiKey,
        experienceKey: drupalSettings.yext.yextOptions.experienceKey,
        locale: drupalSettings.yext.yextOptions.locale,
    };

    // collection of search bar setup options
    // (not an array, object with 0 based index properties)
    const yextSearchBars = drupalSettings.yext.yextSearchBars;

    var yextOnReady = function() {
        $.each(yextSearchBars, function(index, searchBar){
            var url, endpoint, params;

            ANSWERS.addComponent("SearchBar", searchBar.component);

            if (searchBar.placeholderAnimation) {
                endpoint = searchBar.component.verticalKey ? endpointVertical : endpointAll;
                params = $.extend({}, autocompleteParams);
                if (searchBar.component.verticalKey) {
                    params.verticalKey = searchBar.component.verticalKey;
                }

                url = api + endpoint + '?' + $.param(params);
                axios.get(url).then(function(response) {
                    var strings;
                    if (response.data && response.data.response && response.data.response.results) {
                        strings = response.data.response.results.map(function(r) {
                            return r.value;
                        });
                    }
                    if (strings && strings.length) {
                        var options = $.extend({strings: strings}, typedOptions);
                        var typed = new Typed(searchBar.component.container + " .js-yext-query", options);
                    }
                });
            }
        });
    };

    var yextConfig = drupalSettings.yext.yextOptions;
    yextConfig.onReady = yextOnReady;

    var doOnce = true;

    // overcome an issue where sometimes Drupal.behaviors.yextSearchbars.attach() would run before ANSWERS was present
    var initAnswers = function() {

        if (doOnce && ANSWERS) {
            doOnce = false;
            ANSWERS.init(yextConfig);
        }
    };
    window.initAnswers = initAnswers;

    Drupal.behaviors.yextSearchbars =  {
        attach: function (context, settings) {
            $(document, context).once('yextSearchbars').each( function() {
                initAnswers();
            });
        }
    };
} (jQuery, Drupal, drupalSettings));

