import type { AccentColor } from '@/lib/accent-colors';
import type { SavingsBucketIcon } from '@/lib/savings-bucket-icons';

export interface ISavingsBucket {
    id: number;
    name: string;
    target_amount: number;
    current_amount: number;
    color: AccentColor;
    icon: SavingsBucketIcon;
    created_at: string;
    updated_at: string;
}

export type ISavingsBucketForm = {
    name: string;
    target_amount: number;
    color?: AccentColor;
    icon?: SavingsBucketIcon;
};
