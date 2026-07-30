import { Employment } from "@/feature/employee/types";
import { CONTRACT_TYPES } from "../constants/options";
import { Dot } from "@/components/dot";
import { StatusChip } from "@/feature/employee/components/table/status-chipt";
import { format } from "date-fns";
import { ptBR } from "date-fns/locale";

type Props ={
    contract:Employment
}

export function ContractsRow({contract}:Props) {

    const contractType = CONTRACT_TYPES.find((type) => type.value === contract.kind)
    const Icon = contractType?.icon

    const registerAt = contract.registerAt
        ? format(new Date(contract.registerAt), "dd/MM/yyyy", { locale: ptBR })
        : null

    return (
        <div className="bg-transparent border border-border rounded-xl px-4 py-4 flex flex-row gap-2 items-center justify-between">
            <div className="flex flex-row items-center gap-2">
                <div className="dark:text-accent bg-success/10 text-sm rounded-md dark:bg-muted p-2 flex size-9  justify-center  ">
                    {Icon && <Icon />}
                </div>
                <div className="flex flex-col">
                    <div className="flex flex-row items-center gap-1 font-semibold">
                        <h4>{contractType?.label}</h4>
                        {contract.workload && (
                            <>
                                <Dot className="text-[4px]"/>
                                {contract.workload.description} · {contract.workload.weeklyHours}h
                            </>
                        )}
                    </div>
                    {registerAt && (
                        <span className="font-light text-sm dark:text-[#8C948B]">Desde {registerAt}</span>
                    )}
                </div>
            </div>
            <StatusChip status={contract.status}/>
        </div>
    )
}