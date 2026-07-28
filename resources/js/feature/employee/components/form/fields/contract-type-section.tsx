import { EmploymentType } from "@/types/employment/types"
import { Checkbox } from "@/components/ui/checkbox"
import { Field, FieldError, FieldLabel, FieldLegend } from "@/components/ui/field"
import { cn } from "@/lib/utils"
import { CONTRACT_TYPES } from "../constants/options"
import { ButtonWithDescription } from "../button-with-description"
import { Controller, useFormContext } from "react-hook-form"
import { EmployeePayload } from "@/feature/employee/types"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { RHF } from "@/components/rhf-fields";
import { WorkloadSelect } from "../workload-select";

interface ContractTypeSectionProps {
    kinds?: EmploymentType[]
    showProbationary?: boolean
}


export function ContractTypeSection({
    kinds,
    showProbationary = true,
}: ContractTypeSectionProps) {
    const {control} = useFormContext<EmployeePayload>()
    const contractTypes = kinds
        ? CONTRACT_TYPES.filter((type) => kinds.includes(type.value))
        : CONTRACT_TYPES

    return (
        <Card className="flex flex-col gap-3 rounded-xl bg-card">
            <CardHeader>
                <CardTitle>Vínculo</CardTitle>
            </CardHeader>
           
            <CardContent className="gap-4 flex flex-col">
                <Controller
                    control={control}
                    name="kind"
                    render={({field, fieldState}) => (
                        <>
                            <Field
                                data-invalid={fieldState.invalid}
                                className={cn(
                                    "grid grid-cols-2 gap-3",
                                    contractTypes.length >= 4 ? "sm:grid-cols-4" : "sm:grid-cols-3"
                                )}
                            >
                                {contractTypes.map((type) => {
                                    const Icon = type.icon

                                    return (
                                        <ButtonWithDescription
                                            key={type.value}
                                            value={type.value}
                                            label={type.label}
                                            description={type.description}
                                            isSelected={field.value === type.value}
                                            onChange={field.onChange}
                                            icon={<Icon/>}
                                        />
                                    )
                                })}
                            </Field>
                            <FieldError errors={[fieldState.error]} />
                        </>
                    )}
                />

                {showProbationary && (
                    <RHF.Checkbox
                        control={control}
                        name="status"
                        label="Contrato de experiência"
                    />
                )}

                <WorkloadSelect name="workloadId"/>
            </CardContent>
        </Card>
    )
}
