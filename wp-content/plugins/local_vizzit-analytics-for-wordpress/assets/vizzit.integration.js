$vizzit$ = typeof $vizzit$ != "undefined" ? $vizzit$ : {};
$vizzit$ = {
    keys: $vizzit$.keys || {},
    client: $vizzit$.client || {},
    config: $vizzit$.config || {},
    endpoint: $vizzit$.endpoint || {},
};

$vizzit$.integration = {
    host: "https://cdn.vizzit.se/integration/",
    run: function () {
        if ($vizzit$.lib.window.loaded)
            $vizzit$.lib.script();
        else
            $vizzit$.lib.window.onload();
    },

};

$vizzit$.lib = {
    script: function () {
        var url = $vizzit$.integration.host;
        var script = document.createElement("script");
        script.setAttribute("type", "text/javascript");
        script.setAttribute("src", url);
        document.getElementsByTagName("head")[0].appendChild(script);
    },
    window: {
        onload: function () {
            window.addEventListener("load", $vizzit$.lib.script);
        },
        loaded: function () {
            return document.readyState === "complete";
        },
    },
};
