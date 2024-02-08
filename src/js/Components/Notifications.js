import { SnackbarList } from '@wordpress/components';
import { store as NoticesStore } from "@wordpress/notices";
import {useDispatch, useSelect} from '@wordpress/data';

export default function Notifications() {
	const notices = useSelect(
		( select ) => select( NoticesStore ).getNotices(),
		[]
	);
	const { removeNotice } = useDispatch( NoticesStore );
	const snackbarNotices = notices.filter( ({ type }) => type === 'snackbar' );

	return (
		<SnackbarList
			notices={ snackbarNotices }
			className="components-editor-notices__snackbar"
			onRemove={ removeNotice }
		/>
	);
}
