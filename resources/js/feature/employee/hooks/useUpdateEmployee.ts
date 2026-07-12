import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";

export type UpdateEmployeePayload = {
    status: string;
    workloadId: string;
    kind: string;
};

export function useUpdateEmployee() {
    const { put, setData, processing } = useHttp<UpdateEmployeePayload, Employee>();

    async function update(id: string, payload: UpdateEmployeePayload) {
        setData(payload);
        return await put(`/api/v1/employees/${id}`);
    }

    return { update, isUpdatingEmployee: processing };
}
