import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ContractsRow } from "./contracts-row";
import { useEmployeeContext } from "../hooks/useEmployeeContext";
import { Employment } from "@/feature/employee/types";

type Props ={
    contracts:Employment[]
}

export function OtherContracts({contracts}:Props) {

    if(contracts.length <=0) return null

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    Historico de vinculos
                </CardTitle>
                <CardDescription>
                    Cada Mudança cria um novo registro - nada é sobrescrito
                </CardDescription>
            </CardHeader>
            
            <CardContent className="flex flex-col gap-2">
                {
                    contracts.map((v,i)=>(
                        <ContractsRow key={i} contract={v}/>
                    ))
                    
                }
            </CardContent>
        </Card>
    );
}