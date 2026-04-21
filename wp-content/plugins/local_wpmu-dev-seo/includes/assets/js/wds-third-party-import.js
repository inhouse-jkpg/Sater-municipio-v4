import React from "react";
import {render} from "react-dom";
import domReady from "@wordpress/dom-ready";
import ErrorBoundary from "./components/error-boundry";
import ThirdPartyImport from "./components/import/third-party-import";
import Config_Values from "./es6/config-values";

domReady(() => {
	const importContainer = document.getElementById("wds-import-container");
	if (importContainer) {
		const isMultisite = Config_Values.get("is_multisite", "import");
		const nonce = Config_Values.get("nonce", "import");
		const hasAioSeoData = Config_Values.get("aioseop_data_exists", "import");
		const indexSettingsUrl = Config_Values.get("index_settings_url", "import");

		render(
			<ErrorBoundary>
				<ThirdPartyImport
					isMultisite={isMultisite}
					indexSettingsUrl={indexSettingsUrl}
					nonce={nonce}
					hasAioSeoData={hasAioSeoData}
				/>
			</ErrorBoundary>,
			importContainer,
		);
	}
});
