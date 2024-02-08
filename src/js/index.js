import {render} from "@wordpress/element";

import './style.scss';
import SettingsPage from "./Components/SettingsPage";


window.addEventListener(
	'load',
	function () {
		render(
			<SettingsPage />,
			document.getElementById( 'sifg-admin-page' )
		);
	},
	false
);
