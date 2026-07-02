import { z } from "zod"

export const loginSchema = z.object({
  email: z
    .string()
    .nonempty('Um email e necessario!'),
  password: z
    .string()
    .nonempty('É necessário informar a senha!'),
})