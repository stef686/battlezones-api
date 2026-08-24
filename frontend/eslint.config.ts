import pluginVue from 'eslint-plugin-vue';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';

export default defineConfigWithVueTs(
    {
        name: 'battlezones/files',
        files: ['**/*.ts', '**/*.vue'],
    },
    {
        name: 'battlezones/ignores',
        ignores: ['dist/**', 'src/api/schema.d.ts'],
    },
    pluginVue.configs['flat/recommended'],
    vueTsConfigs.recommended,
);
