import {Button, ToggleControl} from "@wordpress/components";
import {__, sprintf} from "@wordpress/i18n";
import {Fragment, useState} from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";

export default function Wizard({location, setWizardStarted}){

	const [selectedOptions, setSelectedOptions] = useState({});

	const toggleOption = function(toggle, value){
		setSelectedOptions({...selectedOptions, [toggle] : value})
	}

	const dispatchImport = function(){
		apiFetch({path: '/sifg/v1/import/dispatch/', data:{location, selectedOptions}, method:'POST'}).finally();
	}

	return (
		<Fragment>
			<p>{sprintf(__('Let\'s import your %s GBP listing website! Select the items you want to import below', 'product-sync-for-gbp'), location.title)}</p>
			<ToggleControl
				label={__('Import posts', 'site-import-for-gbp')}
				help={__('Will import all of your GBP posts with associated images', 'site-import-for-gbp')}
				onChange={(state)=>toggleOption('posts', state)}
				checked={selectedOptions.posts}
			/>
			<ToggleControl
				label={__('Import gallery images', 'site-import-for-gbp')}
				help={__('Import location images into the media library', 'site-import-for-gbp')}
				onChange={(state)=>toggleOption('images', state)}
				checked={selectedOptions.images}
			/>
			<ToggleControl
				label={__('Import reviews', 'site-import-for-gbp')}
				help={__('Import reviews into the Reviews post type', 'site-import-for-gbp')}
				onChange={(state)=>toggleOption('reviews', state)}
				checked={selectedOptions.reviews}
			/>
			<Button onClick={()=>setWizardStarted(false)} isSecondary variant='secondary'>{__('Cancel', 'site-import-for-gbp')}</Button>
			<Button onClick={dispatchImport} isPrimary variant='primary'>{__('Next', 'site-import-for-gbp')}</Button>
		</Fragment>

	);
}
