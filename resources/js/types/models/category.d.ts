export type CategoryColor =
    | 'red'
    | 'blue'
    | 'green'
    | 'yellow'
    | 'orange'
    | 'purple';

export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    color: CategoryColor;
    icon: string | null;
    created_at: string;
    updated_at: string;
}
