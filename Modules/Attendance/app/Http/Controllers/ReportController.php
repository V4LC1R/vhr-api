<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Attendance\Http\Requests\Report\ExportReportRequest;
use Modules\Attendance\Http\Requests\Report\ReportFiltersRequest;
use Modules\Attendance\Services\AttendanceReportService;
use Modules\Attendance\Services\ReportExportService;
use Modules\Job\Enums\EmploymentTypeEnum;

class ReportController extends Controller
{
    /**
     * Colunas expostas em cada exportação — chave da linha agregada => rótulo.
     * O JSON de tela cheia devolve o objeto inteiro; só a exportação recorta.
     */
    private const GENERAL_COLUMNS = [
        'registerNumber'  => 'Matrícula',
        'personName'      => 'Colaborador',
        'kind'            => 'Vínculo',
        'workedHoursDecimal' => 'Horas trabalhadas',
        'expectedMinutes' => 'Horas esperadas',
        'balanceMinutes'  => 'Saldo do período',
        'absenceDays'     => 'Faltas',
    ];

    private const ABSENCES_COLUMNS = [
        'registerNumber'         => 'Matrícula',
        'personName'             => 'Colaborador',
        'absenceDays'            => 'Faltas',
        'negativeBalanceMinutes' => 'Horas negativas',
        'balanceMinutes'         => 'Saldo do período',
    ];

    private const DAYLI_WORKERS_COLUMNS = [
        'registerNumber'     => 'Matrícula',
        'personName'         => 'Colaborador',
        'kind'               => 'Vínculo',
        'diasTrabalhados'    => 'Dias trabalhados',
        'workedHoursDecimal' => 'Horas trabalhadas',
        'diariaValueTotal'   => 'Valor diárias',
    ];

    public function __construct(
        protected readonly AttendanceReportService $reportService,
        protected readonly ReportExportService $exportService,
    ) {
    }

    public function hoursSummary(ReportFiltersRequest $request)
    {
        $this->authorizeGeneral();

        return response()->json([
            'data' => $this->reportService->summary(currentCompany()->companyId, $request->filters()),
        ]);
    }

    public function exportHoursSummary(ExportReportRequest $request)
    {
        $this->authorizeGeneral();

        return $this->exportService->export(
            $request->string('format')->toString(),
            $this->reportService->summary(currentCompany()->companyId, $request->filters()),
            self::GENERAL_COLUMNS,
            'resumo-de-horas',
            'Resumo de horas'
        );
    }

    public function absences(ReportFiltersRequest $request)
    {
        $this->authorizeOwner();

        return response()->json([
            'data' => $this->reportService->summary(
                currentCompany()->companyId,
                $request->filters() + ['onlyExceptions' => true]
            ),
        ]);
    }

    public function exportAbsences(ExportReportRequest $request)
    {
        $this->authorizeOwner();

        return $this->exportService->export(
            $request->string('format')->toString(),
            $this->reportService->summary(
                currentCompany()->companyId,
                $request->filters() + ['onlyExceptions' => true]
            ),
            self::ABSENCES_COLUMNS,
            'faltas-e-horas-negativas',
            'Faltas e horas negativas'
        );
    }

    public function dayliWorkers(ReportFiltersRequest $request)
    {
        $this->authorizeOwner();

        return response()->json([
            'data' => $this->reportService->summary(
                currentCompany()->companyId,
                $request->filters() + ['kinds' => $this->temporaryKinds()]
            ),
        ]);
    }

    public function exportDayliWorkers(ExportReportRequest $request)
    {
        $this->authorizeOwner();

        return $this->exportService->export(
            $request->string('format')->toString(),
            $this->reportService->summary(
                currentCompany()->companyId,
                $request->filters() + ['kinds' => $this->temporaryKinds()]
            ),
            self::DAYLI_WORKERS_COLUMNS,
            'diaristas-e-temporarios',
            'Diaristas e temporários'
        );
    }

    /**
     * Diaristas + temporários + freelancers — mesmo agrupamento "temps" já usado
     * na fila de aprovações, porque só o vínculo `dayli` tem diária calculada
     * automaticamente; os outros dois precisam das horas decimais pra cálculo manual.
     */
    private function temporaryKinds(): array
    {
        return [
            EmploymentTypeEnum::DAYLI->value,
            EmploymentTypeEnum::TEMPORARY->value,
            EmploymentTypeEnum::FREELANCER->value,
        ];
    }

    private function authorizeGeneral(): void
    {
        abort_unless(
            currentCompany()?->can('attendance.dailyEngagements.view'),
            403
        );
    }

    private function authorizeOwner(): void
    {
        abort_unless(
            currentCompany()?->hasRole('owner'),
            403
        );
    }
}
