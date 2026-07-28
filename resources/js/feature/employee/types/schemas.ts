import { z } from 'zod';
import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from '@/types/employment/types';

export const employeeBaseSchema = z.object({
    companyId: z.uuid(),
    workloadId: z.uuid(),
    kind: z.enum(EMPLOYMENT_TYPES),
    status: z.enum(EMPLOYMENT_STATUSES),
    personId: z.uuid(),
})

export const employeeSchema = employeeBaseSchema
  .omit({ personId: true })
  .extend({
    personId: z.uuid().optional(),
    name: z.string().max(255).optional(),
    email: z.email().max(255).optional(),
    cellphone: z.string().max(20).optional(),
    pixKey: z.string().max(255).optional(),
    cpf: z.string().max(255).optional(),
    birthdate: z.date().optional(),
  })
  .superRefine((data, ctx) => {
    if (data.personId) return;

    if(!data.birthdate){
      ctx.addIssue({ code: 'custom', message: 'Data de nascimento é obrigatório', path: ['birthdate'] });
    }

    if(!data.cpf){
      ctx.addIssue({ code: 'custom', message: 'CPF é obrigatório', path: ['cpf'] });
    }

    if (!data.name) {
      ctx.addIssue({ code: 'custom', message: 'Nome é obrigatório', path: ['name'] });
    }
    if (!data.email) {
      ctx.addIssue({ code: 'custom', message: 'E-mail é obrigatório', path: ['email'] });
    }
    if (!data.cellphone) {
      ctx.addIssue({ code: 'custom', message: 'Celular é obrigatório', path: ['cellphone'] });
    }
  });

export type EmployeePayload = z.infer<typeof employeeSchema>;