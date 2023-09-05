import manifest from '@neos-project/neos-ui-extensibility';

import {Field} from '../lib';

manifest('@sitegeist/stonetablet-editors', {}, (globalRegistry) => {
	const editorsRegistry = globalRegistry.get('@sitegeist/inspectorgadget/editors');

	editorsRegistry.set(
		'Sitegeist\\StoneTablet\\Domain\\Field',
		Field
	);
});
