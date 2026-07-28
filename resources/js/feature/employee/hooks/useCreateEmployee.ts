import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";
import { EmployeePayload } from "../types";


export function useCreateEmployee() {
    const { post, setData, processing } = useHttp<EmployeePayload, Employee>();

    async function create(payload: EmployeePayload) {
        setData(payload);
        return await post("/api/v1/employees");
    }

    return { create, isCreatingEmployee: processing };
}
