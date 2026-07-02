import { z } from "zod";
import { loginSchema } from "../schemas/login-schema";

export type LoginForm = z.infer<typeof loginSchema>;

export type LoginResponse = {
    user: unknown; // TODO: tipar com o resource de User quando definir
    companyLogged: boolean;
}