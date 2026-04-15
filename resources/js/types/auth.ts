export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Branch = {
    id: number;
    name: string;
    code: string;
    city: string | null;
    is_headquarters: boolean;
    is_active: boolean;
};

export type Auth = {
    user: User;
    activeBranch: Branch | null;
    branches: Branch[];
    canViewAllBranches: boolean;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
