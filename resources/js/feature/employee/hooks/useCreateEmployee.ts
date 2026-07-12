import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";

export type CreateEmployeePayload = {
    companyId: string;
    personId: string;
    workloadId: string;
    kind: string;
    isProbationary: boolean;
};

export function useCreateEmployee() {
    const { post, setData, processing } = useHttp<CreateEmployeePayload, Employee>();

    async function create(payload: CreateEmployeePayload) {
        setData(payload);
        return await post("/api/v1/employees");
    }

    return { create, isCreatingEmployee: processing };
}
