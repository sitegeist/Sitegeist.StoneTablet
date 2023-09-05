import * as React from 'react';

export function* validator(field: any) {
    if (!field.name) {
        yield {
            field: 'name',
            message: 'Name is required'
        };
    }
}

export const Preview: React.FC<{
    value: any
    api: any
}> = props => {
    const {IconCard} = props.api;

    return (
      <IconCard
        icon="envelope"
        title={props.value.name}
      />
    );
}

export const Form: React.FC<{
    api: any
}> = props => {
const {Field, Layout} = props.api;

	return (
		<Layout.Stack>
			<Layout.Columns columns={2}>
				<Field
					name="name"
					label="Name"
					editor="Neos.Neos/Inspector/Editors/TextFieldEditor"
				/>
			</Layout.Columns>
		</Layout.Stack>
	);
}
