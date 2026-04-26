import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import TagActions from '@/components/Tag/TagActions.vue';
import { ITag } from '@/types/models/tag';

export const createColumns = (t: (key: string) => string): ColumnDef<ITag>[] => [
    {
        accessorKey: 'name',
        header: t('generic.labels.name'),
        cell: ({ row }) => h('p', { class: 'font-medium' }, row.original.name),
    },
    {
        accessorKey: 'description',
        header: t('models.transaction.description'),
        cell: ({ row }) => {
            const description = row.original.description ?? '';
            const shortDescription = description.slice(0, 50) + '…';

            return h(
                'p',
                { class: 'text-sm text-muted-foreground' },
                description.length > 50 ? shortDescription : description,
            );
        },
    },
    {
        accessorKey: 'actions',
        header: () => h('div', { class: 'text-right' }, t('generic.labels.actions')),
        cell: ({ row }) => {
            const tag = row.original;

            return h('div', { class: 'text-right' }, h(TagActions, { tag }));
        },
    },
];
