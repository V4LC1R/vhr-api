import { ChevronLeftIcon, ChevronRightIcon } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Pagination, PaginationContent, PaginationItem } from "@/components/ui/pagination"

interface TablePaginationProps {
    currentPage: number
    lastPage: number
    total: number
    onPrevious: () => void
    onNext: () => void
    isLoading?: boolean
}

export function TablePagination({
    currentPage,
    lastPage,
    total,
    onPrevious,
    onNext,
    isLoading,
}: TablePaginationProps) {
    return (
        <div className="flex items-center justify-between gap-4">
            <span className="text-sm text-muted-foreground">
                {total} {total === 1 ? "registro" : "registros"}
            </span>
            <Pagination className="mx-0 w-fit">
                <PaginationContent>
                    <PaginationItem>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            disabled={isLoading || currentPage <= 1}
                            onClick={onPrevious}
                            aria-label="Página anterior"
                        >
                            <ChevronLeftIcon />
                        </Button>
                    </PaginationItem>
                    <PaginationItem>
                        <span className="px-2 text-sm text-muted-foreground">
                            {currentPage} de {lastPage}
                        </span>
                    </PaginationItem>
                    <PaginationItem>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            disabled={isLoading || currentPage >= lastPage}
                            onClick={onNext}
                            aria-label="Próxima página"
                        >
                            <ChevronRightIcon />
                        </Button>
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>
    )
}
