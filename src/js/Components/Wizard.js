import {Button, Icon, Spinner, TextareaControl, ToggleControl} from "@wordpress/components";
import {__, sprintf} from "@wordpress/i18n";
import {Fragment, useEffect, useState} from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import {store as noticesStore} from "@wordpress/notices";
import {useDispatch} from "@wordpress/data";
import GoogleLocationSelector from "./GoogleLocationSelector";

export default function Wizard(){
	const {auth_url, is_google_configured} = sifg_localize_admin;


	const [selectedOptions, setSelectedOptions] = useState({});

	const [ isDispatching, setDispatching] = useState(false);

	const [ wizardStep, setWizardStep ] = useState('connect');


	const { createErrorNotice } = useDispatch( noticesStore );

	const [isGoogleConnected, setGoogleConnected ] = useState(is_google_configured);

	const [wizardStarted, setWizardStarted] = useState(false);

	const [wizardLocation, setWizardLocation] = useState(false);

	const [ importInProgress, setImportInProgress ] = useState(false);
	const [ cancelling, setCancelling] = useState(false);
	const [ checkedStatus, setCheckedStatus ] = useState(false);

	const [ importLog, setImportLog] = useState('');

	const toggleOption = function(toggle, value){
		setSelectedOptions({...selectedOptions, [toggle] : value})
	}

	const startWizard = function(location_id) {
		setWizardStarted(true);
		setWizardStep('start');
		setWizardLocation(location_id);
	}

	const disconnectGoogle = function(){
		apiFetch({path: '/sifg/v1/account', method:'DELETE'}).finally();
		setGoogleConnected( false);
	}

	const getImportLog = function(){
		apiFetch({path: '/sifg/v1/import/log/'})
			.then((data) => {
				setImportLog(data.log);
			})
			.catch((error) => {
				createErrorNotice( __('Failed to load import log', 'site-import-for-gbp'), {
					type: 'snackbar',
				} );
			})
			.finally();
	}

	const confirmImport = function(){
		setDispatching(true);
		apiFetch({path: '/sifg/v1/import/confirm/'})
			.then((data) => {
				setWizardStep('connect');
			})
			.catch((error) => {
				createErrorNotice( __('Could not confirm import', 'site-import-for-gbp'), {
					type: 'snackbar',
				} );
			})
			.finally(() => setDispatching(false));
	}

	const cancelImport = function(){
		setCancelling(true);
		apiFetch({path: '/sifg/v1/import/cancel'}).then((data) => {}).catch((error) => {
			createErrorNotice( __('Could not cancel import', 'site-import-for-gbp'), {
				type: 'snackbar',
			} );
		})
			.finally();
	}

	useEffect(() => {
		const checkImportStatus = () => {
			apiFetch({path: '/sifg/v1/import/status'}).then((data) => {
				const importing = !!data.importing;
				const unreviewed_log = !!data.unreviewed_log;
				setImportInProgress(importing);
				if(importing){
					setWizardStep('importing');
				}

				if(!importing && unreviewed_log){
					setWizardStep('completed');
					getImportLog();
				}

			}).catch((error) => {
				createErrorNotice(__("Could not check the state of the import", 'site-import-for-gbp'), {
					type: 'snackbar',
				});
			}).finally(() => {
				if(!importInProgress){
					setCancelling(false);
				}
				setCheckedStatus(true);
			});
		};

		checkImportStatus();

		const interval = setInterval(checkImportStatus, 5000);


		return () => {
			clearInterval(interval);
		};
	}, [importInProgress]);

	const dispatchImport = function(){
		setDispatching(true);
		apiFetch({path: '/sifg/v1/import/dispatch/', data:{location: wizardLocation, selectedOptions}, method:'POST'})
			.then(() => {
				setWizardStep('importing');
				setImportInProgress(true);
			})
			.catch((error) => {
				createErrorNotice( __('Failed to dispatch the import', 'site-import-for-gbp'), {
					type: 'snackbar',
				} );
				setDispatching(false);
			})
			.finally(() => {setDispatching(false)});
	}


	if(!checkedStatus) {
		return (
			<p>
				<Spinner/>{__("Checking if import is currently running", 'site-import-for-gbp')}
			</p>
		);
	}

	if (wizardStep === 'connect') {
		if (isGoogleConnected) {
			return (
				<Fragment>
					<GoogleLocationSelector
						isGoogleConfigured={isGoogleConnected}
						startWizard={startWizard}
					/>
					<Button onClick={disconnectGoogle} isPrimary isDestructive icon={() => <Icon icon='google'/>} variant='primary' className='sifg-disconnect-button'>{__('Disconnect Google account', 'site-import-for-gbp')}</Button>
				</Fragment>
			);
		}else{
			return (
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
		}
	}else if(wizardStep === 'start'){
		return (
			<Fragment>
				<p>{sprintf(__('Let\'s import your %s GBP listing website! Select the items you want to import below', 'product-sync-for-gbp'), wizardLocation.title)}</p>
				<ToggleControl
					label={__('Import posts', 'site-import-for-gbp')}
					help={__('Will import your Google Business Profile posts with associated images', 'site-import-for-gbp')}
					onChange={(state)=>toggleOption('posts', state)}
					checked={selectedOptions.posts}
				/>
				<ToggleControl
					label={__('Import gallery images', 'site-import-for-gbp')}
					help={__('Import location images into the media library', 'site-import-for-gbp')}
					onChange={(state)=>toggleOption('images', state)}
					checked={selectedOptions.images}
				/>
				{/*<ToggleControl*/}
				{/*	label={__('Import reviews', 'site-import-for-gbp')}*/}
				{/*	help={__('Import reviews into the Reviews post type', 'site-import-for-gbp')}*/}
				{/*	onChange={(state)=>toggleOption('reviews', state)}*/}
				{/*	checked={selectedOptions.reviews}*/}
				{/*/>*/}
				<div className='form-buttons'>
					<Button onClick={()=>setWizardStep('connect')} isSecondary variant='secondary'>{__('Cancel', 'site-import-for-gbp')}</Button>
					<Button onClick={dispatchImport} disabled={ isDispatching} isPrimary variant='primary'>					{
						isDispatching ? (
							<>
								<Spinner />
								{__('Starting Import','site-import-for-gbp')}
							</>
						) : __('Next', 'site-import-for-gbp')
					}</Button>
				</div>

			</Fragment>

		);
	}else if(wizardStep === "importing"){
		return (
			<Fragment>
				<p><Spinner/>{__('Import is currently in progress, and running in the background. You may stay here and wait for it to complete or leave this page. Up to you!', 'site-import-for-gbp')}</p>
				<div className='form-buttons'>
					<Button onClick={ cancelImport } isSecondary variant='secondary' disabled={cancelling}>{
						cancelling ? (
								<>
									<Spinner />
									{__('Cancelling...','site-import-for-gbp')}
								</>
							) :
						__('Cancel import', 'site-import-for-gbp')
					}</Button>
				</div>
			</Fragment>
	);
	}else if(wizardStep === "completed"){
		return (
			<>
				<p>{__('The import was completed, please review the log below and confirm', 'site-import-for-gbp')}</p>
				<TextareaControl
					value={ importLog }
					disabled={true}
					style={{height:500}}
				/>
				<Button onClick={confirmImport} disabled={isDispatching} variant='primary' isPrimary>
					{
						isDispatching ? (
							<>
								<Spinner />{__('Confirming', 'site-import-for-gbp')}
							</>
						) : __('Confirm', 'site-import-for-gbp')
					}
				</Button>
			</>
		);
	}

	}
