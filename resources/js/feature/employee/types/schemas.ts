import { z } from 'zod';
import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from '@/types/employment/types';

export const employeeBaseSchema = z.object({
  workloadId: z.uuid({error:'Por favor selecione uma jornada'}).optional(),
  kind: z.enum(EMPLOYMENT_TYPES,{error:'Por favor selecione um Modelo de contratação'}).optional(),
  status: z.enum(EMPLOYMENT_STATUSES),
  personId: z.uuid(),
  isProbationary:z.boolean()
})

export const employeeSchema = employeeBaseSchema
  .omit({ personId: true })
  .extend({
    personId: z.uuid().optional(),
    person:z.object({
      name: z.string().max(255).optional(),
      email: z.email().max(255).optional(),
      cellphone: z.string().max(20).optional(),
      pixKey: z.string().max(255).optional(),
      cpf: z.string().max(255).optional(),
      birthdate: z.date().optional(),
    })
  })
  .superRefine((data, ctx) => {
    if (!data.workloadId) {
      ctx.addIssue({ code: 'custom', message: 'Por favor selecione uma jornada', path: ['workloadId'] });
    }

    if (!data.kind) {
      ctx.addIssue({ code: 'custom', message: 'Por favor selecione um Modelo de contratação', path: ['kind'] });
    }

    if (data.personId) return;

    if(!data.person.birthdate){
      ctx.addIssue({ code: 'custom', message: 'Data de nascimento é obrigatório', path: ['person', 'birthdate'] });
    }

    if(!data.person.cpf){
      ctx.addIssue({ code: 'custom', message: 'CPF é obrigatório', path: ['person', 'cpf'] });
    }

    if (!data.person.name) {
      ctx.addIssue({ code: 'custom', message: 'Nome é obrigatório', path: ['person', 'name'] });
    }
    if (!data.person.email) {
      ctx.addIssue({ code: 'custom', message: 'E-mail é obrigatório', path: ['person', 'email'] });
    }
    if (!data.person.cellphone) {
      ctx.addIssue({ code: 'custom', message: 'Celular é obrigatório', path: ['person', 'cellphone'] });
    }
  });

export type EmployeePayload = z.infer<typeof employeeSchema>;