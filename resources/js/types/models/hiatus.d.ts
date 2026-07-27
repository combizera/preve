export interface IHiatus {
    id?: number;
    started_at: string;
    ended_at: string | null;
    note: string | null;
    reconciled_at?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface IHiatusInput {
    started_at: string;
    ended_at: string | null;
    note: string | null;
}
