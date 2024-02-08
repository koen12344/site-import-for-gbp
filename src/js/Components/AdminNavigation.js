import {useState} from "@wordpress/element";
import {__} from "@wordpress/i18n";

const AdminNavigation = ({setSection}) => {
	const switchTab = (section) => {
		setActiveTab(section);
		setSection(section);
	}

	const [activeTab, setActiveTab] = useState('settings');

	return (
		<nav className='sifg-navigation'>
			<button onClick={() => switchTab('settings')} className={activeTab === 'settings' ? 'is-active' : ''}>
				<span>{__('Import', 'product-sync-for-gbp')}</span>
			</button>
			<button onClick={() => switchTab('triggers')} className={activeTab === 'triggers' ? 'is-active' : ''}>
				<span>{__('Triggers', 'product-sync-for-gbp')}</span>
			</button>
			<button onClick={() => switchTab('log')} className={activeTab === 'log' ? 'is-active' : ''}>
				<span>{__('Log', 'product-sync-for-gbp')}</span>
			</button>
		</nav>
	);
}


export default AdminNavigation;
