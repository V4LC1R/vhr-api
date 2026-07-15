import { z } from 'zod';

/**
 * Roles Spatie seedadas em RolesAndPermissionsSeeder — únicas aceitas pelo back
 * em `role` no create/update de usuário (Rule::in) por enquanto.
 */
export const USER_ROLES = ['owner', 'humanResource', 'accountant', 'employee'] as const;
export type UserRole = (typeof USER_ROLES)[number];

export const USER_ROLE_LABELS: Record<UserRole, string> = {
    owner: 'Owner',
    humanResource: 'RH',
    accountant: 'Contador',
    employee: 'Funcionário',
};

/**
 * Request (entrada) — StoreUserRequest.
 */
export const createUserSchema = z.object({
    email: z.email('E-mail inválido'),
    password: z.string().min(8, 'A senha deve ter ao menos 8 caracteres'),
    role: z.enum(USER_ROLES, { message: 'Selecione um papel' }),
    personId: z.uuid().optional().nullable(),
});
export type CreateUserPayload = z.infer<typeof createUserSchema>;

/**
 * Request (entrada) — UpdateUserRequest. `password` vazio = mantém a atual
 * (StoreUserRequest exige mínimo 8, mas aqui o campo é opcional).
 */
export const updateUserSchema = z.object({
    email: z.email('E-mail inválido'),
    password: z
        .string()
        .refine((value) => value === '' || value.length >= 8, 'A senha deve ter ao menos 8 caracteres'),
    role: z.enum(USER_ROLES).optional(),
    status: z.enum(['active', 'inactive']),
});
export type UpdateUserPayload = z.infer<typeof updateUserSchema>;
