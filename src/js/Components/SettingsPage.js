import {Fragment, useEffect, useState} from "@wordpress/element";
import Header from "./Header";
import {
	Button, Icon,
	PanelBody,
	PanelRow, Spinner,
} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import GoogleLocationSelector from "./GoogleLocationSelector";
import apiFetch from "@wordpress/api-fetch";

import {useDispatch} from "@wordpress/data";
import {store as noticesStore} from "@wordpress/notices";
import Notifications from "./Notifications";
import Wizard from "./Wizard";
import MulsiteWarning from "./MultisiteWarning";

const {plugin_url, support_url, auth_url, is_google_configured, is_multisite} = sifg_localize_admin;



export default function SettingsPage(){
	const { createErrorNotice } = useDispatch( noticesStore );

	// const [pluginSettings, setPluginSettings] = useState({});
	//
	// const [isSavingSettings, setSavingSettings] = useState(false);
	//
	// const [settingsLoaded, setSettingsLoaded] = useState(false);









	return (
		<Fragment>
			<Header/>
			<div className="sifg-settings-main">
				<div className="sifg-left">
					<PanelBody
						title={ __( 'Import wizard', 'site-import-for-gbp' ) }
					>
						<PanelRow>
							<div className="sifg-connect">
								{
									is_multisite ? <MulsiteWarning /> : <Wizard/>
								}

							</div>
						</PanelRow>
					</PanelBody>
					<PanelBody>
						<div className="sifg-info">
							<img src={ plugin_url + "img/koen.png" } alt="Photo of Koen"/>
							<h2>{ __( 'Got a question?', 'site-import-for-gbp' ) }</h2>

							<p>{ __( 'Don\'t hesitate to reach out if you need any help.', 'site-import-for-gbp' ) }<br /><br /> ~ Koen</p>
							<div className='form-buttons'>
								<Button
									isPrimary
									href='mailto:koen@tycoonwp.com'
								>
									{ __( 'Ask a question', 'site-import-for-gbp' ) }
								</Button>
								<Button
									isTertiary
									href='https://docs.digitaldistortion.dev/collection/32-site-import-for-gbp'
									icon={() => <Icon icon='book' />}
									target='_blank'
								>
									{ __( 'Docs', 'site-import-for-gbp' ) }
								</Button>
								<Button
									isTertiary
									href='https://twitter.com/koenreus'
									icon={() => <Icon icon='twitter' />}
									target='_blank'
								>
									{ __( 'Follow me on X', 'site-import-for-gbp' ) }
								</Button>
							</div>
						</div>
					</PanelBody>
				</div>
			</div>
			<Notifications />
		</Fragment>
)
}
