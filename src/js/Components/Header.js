import {__} from "@wordpress/i18n";
import AdminNavigation from "./AdminNavigation";
import {useState} from "@wordpress/element";

const {plugin_url} = sifg_localize_admin;
export default function Header(){

	const [section, setSection] = useState('settings');

	return (
		<div className="sifg-header">
			<div className="sifg-container">
				<div className="sifg-logo">
					<img src={plugin_url + "img/icon.png"} alt="Plugin icon" width='60'/>
					<h1>{__('Site Import for GBP', 'site-import-for-gbp')}</h1>
				</div>
				{/*<AdminNavigation setSection={setSection}/>*/}
			</div>
		</div>
	);
}
