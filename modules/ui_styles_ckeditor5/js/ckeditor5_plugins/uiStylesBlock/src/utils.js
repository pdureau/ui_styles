export function normalizeConfig(styleDefinitions = []) {
	const normalizedDefinitions = [];

  for (const definition of styleDefinitions) {
    definition.options.forEach(style_option => {
      const originalStyleOptionName = style_option.name;

      style_option.name = `${definition.id}:${originalStyleOptionName}`;
      normalizedDefinitions.push({...style_option});
    });
	}
	return normalizedDefinitions;
}
