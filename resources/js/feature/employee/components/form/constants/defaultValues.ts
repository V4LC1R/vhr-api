import { EmployeePayload } from "@/feature/employee/types";

export const defaultEmployeeValues:EmployeePayload = {
    isProbationary:true,
    kind:"clt",
    workloadId:'',
    status:"experience",
    person:{
        name:'',
        email:'',
        cellphone:'',
        pixKey:'',
        cpf:'',
        birthdate:undefined,
    },
}