import {Fragment, useEffect, useState} from "@wordpress/element";
import Header from "./Header";
import {
	Button, Icon,
	PanelBody,
	PanelRow,
} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import GoogleLocationSelector from "./GoogleLocationSelector";
import apiFetch from "@wordpress/api-fetch";

import {useDispatch} from "@wordpress/data";
import {store as noticesStore} from "@wordpress/notices";
import Notifications from "./Notifications";
import Wizard from "./Wizard";

const {plugin_url, support_url, auth_url, is_google_configured } = sifg_localize_admin;



export default function SettingsPage(){
	const { createErrorNotice } = useDispatch( noticesStore );

	const [pluginSettings, setPluginSettings] = useState({});

	const [isSavingSettings, setSavingSettings] = useState(false);

	const [settingsLoaded, setSettingsLoaded] = useState(false);

	const [isGoogleConnected, setGoogleConnected ] = useState(is_google_configured);

	const [wizardStarted, setWizardStarted] = useState(false);

	const [wizardLocation, setWizardLocation] = useState(false);
	const updateSetting = function(option, value){
		const newvalue = {...pluginSettings, [option]: value};
		setPluginSettings(newvalue);

		saveSettings(newvalue);
	}

	const saveSettings = function(settings){
		setSavingSettings(true);

		apiFetch({path: '/sifg/v1/settings', data:{settings}, method:'POST'}).finally(() => setSavingSettings(false));
	}

	const disconnectGoogle = function(){
		apiFetch({path: '/sifg/v1/account', method:'DELETE'}).finally();
		setGoogleConnected( false);
	}

	const startWizard = function(location_id) {
		setWizardStarted(true);
		setWizardLocation(location_id);
	}
	//
	// useEffect(() => {
	// 	apiFetch({path: '/sifg/v1/settings'}).then((data) => {
	// 		setPluginSettings({...pluginSettings, ...data});
	// 		setSettingsLoaded(true);
	// 	}).catch(() => {
	// 		createErrorNotice(__("Could not load plugin settings", 'product-sync-for-gbp'), {
	// 			type: 'snackbar',
	// 		} );
	// 	});
	// }, []);


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
								{ !wizardStarted ? !isGoogleConnected ?
									(
										<Fragment>
											<p>{__('Click the button below to connect the plugin to the Google account that contains the business location of which you want to import the website data.', 'site-import-for-gbp')}</p>
											<Button
												isPrimary
												variant="primary"
												icon={() => <Icon icon='google'/>}
												href={auth_url}
											>{__('Connect to Google Business Profile', 'site-import-for-gbp')}</Button>
										</Fragment>
									)
									:
									(
										<Fragment>
											<GoogleLocationSelector
												isGoogleConfigured={isGoogleConnected}
												startWizard={startWizard}
											/>
											<Button onClick={disconnectGoogle} isPrimary isDestructive icon={() => <Icon icon='google'/>} variant='primary' className='sifg-disconnect-button'>{__('Disconnect Google account', 'site-import-for-gbp')}</Button>
										</Fragment>
									)
									:
									(
										<Wizard location={wizardLocation} setWizardStarted={setWizardStarted}/>
									)
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
									href={ support_url }
								>
									{ __( 'Ask a question', 'site-import-for-gbp' ) }
								</Button>
								<Button
									isTertiary
									href='https://docs.digitaldistortion.dev/collection/15-product-sync-for-gbp'
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
