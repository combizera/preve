export interface IUser {
    id: number;
    name: string;
    email: string;
    locale: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}
