import { useEffect, useState } from "react"
import { router } from "@inertiajs/react"
import toast from "react-hot-toast"

import { useAuth } from "@/hooks/use-auth"
import { extractErrorMessage, extractFieldErrors } from "@/lib/http"
import { EmploymentType } from "@/types/employment/types"
import { Person } from "@/feature/person/types/types"
import { personSchema } from "@/feature/person/types/schemas"
import { useCreatePerson } from "@/feature/person/hooks/useCreatePerson"
import { Workload } from "@/feature/workload/types/types"
import { useCreateEmployee } from "./useCreateEmployee"
import { useNextRegisterNumber } from "./useNextRegisterNumber"

type PersonErrors = { cpf?: string; name?: string; email?: string; cellphone?: string }

export function useEmployeeForm() {
    const { current } = useAuth()
    const { fetchNextRegisterNumber, isLoadingRegisterNumber, registerNumber } = useNextRegisterNumber()

    const [cpf, setCpf] = useState("")
    const [name, setName] = useState("")
    const [email, setEmail] = useState("")
    const [cellphone, setCellphone] = useState("")
    const [matchedPerson, setMatchedPerson] = useState<Person | null>(null)
    const [personErrors, setPersonErrors] = useState<PersonErrors>({})

    const [selectedWorkload, setSelectedWorkload] = useState<Workload | null>(null)
    const [workloadIdError, setWorkloadIdError] = useState<string>()

    const [kind, setKind] = useState<EmploymentType | null>(null)
    const [kindError, setKindError] = useState<string>()

    const { create: createPerson, isCreatingPerson } = useCreatePerson()
    const { create: createEmployee, isCreatingEmployee } = useCreateEmployee()

    useEffect(() => {
        fetchNextRegisterNumber()
    }, [])

    function handleMatch(person: Person | null) {
        setMatchedPerson(person)
        if (person) {
            setName(person.name)
            setEmail(person.email)
            setCellphone(person.cellphone)
            setPersonErrors({})
        }
    }

    function selectWorkload(workload: Workload) {
        setSelectedWorkload(workload)
        setWorkloadIdError(undefined)
    }

    function selectKind(next: EmploymentType) {
        setKind(next)
        setKindError(undefined)
    }

    async function resolvePersonId(): Promise<string | null> {
        if (matchedPerson) return matchedPerson.id

        const digits = cpf.replace(/\D/g, "")
        const result = personSchema.safeParse({ cpf: digits, name, email, cellphone })
        if (!result.success) {
            const fieldErrors: PersonErrors = {}
            for (const issue of result.error.issues) {
                const key = issue.path[0] as keyof PersonErrors
                fieldErrors[key] = issue.message
            }
            setPersonErrors(fieldErrors)
            return null
        }
        setPersonErrors({})

        try {
            const person = await createPerson(result.data)
            return person.id
        } catch (error) {
            const fieldErrors = extractFieldErrors(error) as PersonErrors
            if (Object.keys(fieldErrors).length > 0) {
                setPersonErrors(fieldErrors)
            } else {
                toast.error(extractErrorMessage(error, "Não foi possível cadastrar a pessoa."))
            }
            return null
        }
    }

    async function submit() {
        if (!current?.companyId) {
            toast.error("Empresa ativa não encontrada.")
            return
        }

        const personId = await resolvePersonId()
        if (!personId) return

        if (!selectedWorkload) {
            setWorkloadIdError("Selecione ou cadastre uma jornada")
            return
        }

        if (!kind) {
            setKindError("Selecione o tipo de contratação")
            return
        }

        try {
            await createEmployee({
                companyId: current.companyId,
                personId,
                workloadId: selectedWorkload.id,
                kind,
            })
            toast.success("Colaborador cadastrado com sucesso!")
            router.visit("/dashboard/employees")
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível cadastrar o colaborador."))
        }
    }

    return {
        name,
        registerNumber,
        isLoadingRegisterNumber,

        cpf,
        setCpf,
        setName,
        email,
        setEmail,
        cellphone,
        setCellphone,
        matchedPerson,
        handleMatch,
        personErrors,

        selectedWorkloadId: selectedWorkload?.id ?? null,
        selectWorkload,
        workloadIdError,

        kind,
        selectKind,
        kindError,

        submit,
        isSubmitting: isCreatingPerson || isCreatingEmployee,
    }
}
