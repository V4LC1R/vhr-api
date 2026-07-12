import { useEffect, useState } from "react"
import { router } from "@inertiajs/react"
import toast from "react-hot-toast"

import { extractErrorMessage, extractFieldErrors } from "@/lib/http"
import { personSchema } from "@/feature/person/types/schemas"
import { useUpdatePerson } from "@/feature/person/hooks/useUpdatePerson"
import { Workload } from "@/feature/workload/types/types"
import { EmploymentType } from "@/types/employment/types"
import { Employee } from "../types/types"
import { useGetEmployee } from "./useGetEmployee"
import { useUpdateEmployee } from "./useUpdateEmployee"
import { useDismissEmployee } from "./useDismissEmployee"

type PersonErrors = { name?: string; email?: string; cellphone?: string; pixKey?: string }

export function useEmployeeEditForm(employeeId: string) {
    const [employee, setEmployee] = useState<Employee | null>(null)
    const [isLoading, setIsLoading] = useState(true)

    const [cpf, setCpf] = useState("")
    const [name, setName] = useState("")
    const [email, setEmail] = useState("")
    const [cellphone, setCellphone] = useState("")
    const [pixKey, setPixKey] = useState("")
    const [personErrors, setPersonErrors] = useState<PersonErrors>({})

    const [selectedWorkload, setSelectedWorkload] = useState<Workload | null>(null)
    const [workloadIdError, setWorkloadIdError] = useState<string>()

    const [isProbationary, setIsProbationary] = useState(false)

    const [kind, setKind] = useState<EmploymentType | null>(null)
    const [kindError, setKindError] = useState<string>()

    const [dismissDialogOpen, setDismissDialogOpen] = useState(false)

    const { fetchEmployee, isLoadingEmployee } = useGetEmployee()
    const { update: updatePerson, isUpdatingPerson } = useUpdatePerson()
    const { update: updateEmployee, isUpdatingEmployee } = useUpdateEmployee()
    const { dismiss: dismissEmployee, isDismissing } = useDismissEmployee()

    useEffect(() => {
        let cancelled = false

        async function load() {
            try {
                const data = await fetchEmployee(employeeId)
                if (cancelled) return

                setEmployee(data)
                setCpf(data.person?.cpf ?? "")
                setName(data.person?.name ?? "")
                setEmail(data.person?.email ?? "")
                setCellphone(data.person?.cellphone ?? "")
                setPixKey(data.person?.pixKey ?? "")
                setSelectedWorkload(data.activeEmployment?.workload ?? null)
                setIsProbationary(data.activeEmployment?.status === "experience")
                setKind(data.activeEmployment?.kind ?? null)
            } catch (error) {
                toast.error(extractErrorMessage(error, "Não foi possível carregar o colaborador."))
            } finally {
                if (!cancelled) setIsLoading(false)
            }
        }

        load()
        return () => {
            cancelled = true
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [employeeId])

    function selectWorkload(workload: Workload) {
        setSelectedWorkload(workload)
        setWorkloadIdError(undefined)
    }

    function selectKind(next: EmploymentType) {
        setKind(next)
        setKindError(undefined)
    }

    // `activeEmployment` só existe (relação HasOne filtrada por status hired/experience) enquanto o
    // vínculo está ativo — depois de desligado, esse campo vem null, não com status "left".
    const isDismissed = !!employee && !employee.activeEmployment

    async function submit() {
        if (!employee) return

        const result = personSchema.safeParse({ cpf, name, email, cellphone, pixKey: pixKey || undefined })
        if (!result.success) {
            const fieldErrors: PersonErrors = {}
            for (const issue of result.error.issues) {
                const key = issue.path[0] as keyof PersonErrors
                fieldErrors[key] = issue.message
            }
            setPersonErrors(fieldErrors)
            return
        }
        setPersonErrors({})

        if (!selectedWorkload) {
            setWorkloadIdError("Selecione ou cadastre uma jornada")
            return
        }

        if (!kind) {
            setKindError("Selecione o tipo de contratação")
            return
        }

        try {
            await updatePerson(employee.personId, result.data)
            await updateEmployee(employeeId, {
                status: isProbationary ? "experience" : "hired",
                workloadId: selectedWorkload.id,
                kind,
            })
            toast.success("Colaborador atualizado com sucesso!")
            router.visit("/dashboard/employees")
        } catch (error) {
            const fieldErrors = extractFieldErrors(error) as PersonErrors
            if (Object.keys(fieldErrors).length > 0) {
                setPersonErrors(fieldErrors)
            } else {
                toast.error(extractErrorMessage(error, "Não foi possível atualizar o colaborador."))
            }
        }
    }

    async function confirmDismiss() {
        try {
            await dismissEmployee(employeeId)
            toast.success("Colaborador desligado com sucesso!")
            setDismissDialogOpen(false)
            router.visit("/dashboard/employees")
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível desligar o colaborador."))
        }
    }

    return {
        employee,
        isLoading: isLoading || isLoadingEmployee,
        isDismissed,

        cpf,
        name,
        setName,
        email,
        setEmail,
        cellphone,
        setCellphone,
        pixKey,
        setPixKey,
        personErrors,

        selectedWorkloadId: selectedWorkload?.id ?? null,
        selectWorkload,
        workloadIdError,

        isProbationary,
        setIsProbationary,

        kind,
        selectKind,
        kindError,

        dismissDialogOpen,
        setDismissDialogOpen,
        confirmDismiss,
        isDismissing,

        submit,
        isSubmitting: isUpdatingPerson || isUpdatingEmployee,
    }
}
