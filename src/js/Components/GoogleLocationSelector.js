import {Button, CheckboxControl, ExternalLink, Icon, Notice, SelectControl, Spinner} from "@wordpress/components";
import {useEffect, useState} from "@wordpress/element";
import {__, sprintf} from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {addQueryArgs} from "@wordpress/url";


export default function GoogleLocationSelector({ isGoogleConfigured, startWizard}){
	const [ group, setGroup ] = useState( '' );

	const [ groups, setGroups ] = useState([]);

	const [ pageToken, setPageToken ] = useState(null);

	const [ locations, setLocations] = useState([]);

	const [ hasResolved, setResolved] = useState(false);

	const [ error, setError ] = useState();

	useEffect(() => {
		if(isGoogleConfigured){
			setError(null);
			setResolved(false);
			apiFetch({path: '/sifg/v1/account/groups'}).then((data) => {
				setGroups(data);
				getGroup(data[0].value);
			}).catch((error) => {
				setError(error.message);
			});
		}
	}, [isGoogleConfigured]);

	const getGroup = function(group, pageToken, refresh){
		setResolved(false);
		setPageToken(pageToken);
		setGroup(group);
		apiFetch({path: addQueryArgs('/sifg/v1/account/groups/locations', {
				'group_id': group,
				'nextPageToken': pageToken,
				'refresh': refresh,
			})
		}).then((data) => {
			setLocations(data);
		}).catch((error) => {
			setError(error.message);
		}).finally(() => {
			setResolved(true);
		});
	}


	if(!isGoogleConfigured) {
		return (
			<div className='sifg-location-selector'>
				{__('No account connected', 'site-import-for-gbp')}
			</div>
		);
	}else if(error){
		return <Notice status="error" isDismissible={false}>{error}</Notice>;
	}else{
		return (
			<div className='sifg-location-selector'>
				<div className='sifg-selector-controls-top'>
					<SelectControl
						label={__('Group', 'site-import-for-gbp')}
						value={ group }
						options={ groups }
						onChange={ ( newGroup ) => getGroup( newGroup ) }
						__nextHasNoMarginBottom
					/>
					<Button isSmall={true} variant="secondary" onClick={()=> getGroup(group, pageToken, true) } icon={() => <Icon icon='update' />}>{__("Refresh locations", "site-import-for-gbp")}</Button>
				</div>
				<LocationList hasResolved={hasResolved} locations={locations} startWizard={startWizard} />
				<Pagination hasResolved={hasResolved} locations={locations} getGroup={getGroup} currentGroup={group} />
			</div>
		);
	}
}

function Pagination({hasResolved, locations, getGroup, currentGroup}){
	const [ prevPages, setPrevPages ] = useState([null]);

	const switchPage = (back) => {
		if(back){
			const lastItem = prevPages[prevPages.length - 2];
			setPrevPages(prevArray => prevArray.slice(0, -1));
			getGroup(currentGroup, lastItem);
			return;
		}
		setPrevPages(prevArray => [...prevArray, locations.nextPageToken]);
		getGroup(currentGroup, locations.nextPageToken);

	};

	if ( ! hasResolved ) {
		return false;
	}
	return (
		<div className="sifg-selector-controls-bottom">
			{prevPages.length > 1 && <Button variant="secondary" isSmall={true} onClick={() => switchPage(true) }>{__('Previous page', 'site-import-for-gbp')}</Button>}
			{locations.nextPageToken && <Button variant="secondary" isSmall={true} onClick={ () => switchPage(false) }>{__('Next page', 'site-import-for-gbp')}</Button>}
		</div>
	);
}


function LocationList({hasResolved, locations, startWizard}){
	if ( ! hasResolved ) {
		return <Spinner />;
	}



	return (
		<table className="wp-list-table widefat striped table-view-list">

			<tbody>
			{ locations.locations?.map(location => (
				<tr key={ location.name }>
					<td>{location.title }</td>
					<td align='right'>
						<div className='form-buttons'>
							<Button isPrimary onClick={() => startWizard(location)} variant='primary' icon={() => <Icon icon='migrate' />}>{__('Start GBP website import', 'site-import-for-gbp')}</Button>
							{/*{!multiple && <MakeDefaultLocationButton selected={isLocationSelected(location)} location={location} setLocation={setLocation}/>}*/}
							{/*<Button target='_blank' href={ 'https://business.google.com/products/l/' + location.id } variant='secondary' icon={() => <Icon icon='external' />}>{__('View products on Google', 'product-sync-for-gbp')}</Button>*/}
						</div>
					</td>
				</tr>
			))}
			</tbody>
		</table>
	);
}

