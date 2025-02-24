<?php

namespace App\Services\TripTicketExporter;

use App\Enums\TripTicketStatus;
use App\Enums\TripTicketTemplateEnum;
use App\Events\TripTickets\ChangeTripTicketStatus;
use App\Models\TripTicket;
use App\Services\TripTicketExporter\Mappers\ItemMapperStrategy;
use App\Services\TripTicketExporter\SheetWriters\SheetWriterStrategy;
use App\ValueObjects\EntityId;
use DomainException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ExcelGenerator
{
    /**
     * @var IReader
     */
    private $reader;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->reader = IOFactory::createReader('Xlsx');
    }

    /**
     * @param array $ids
     * @return string
     * @throws \Exception
     */
    public function generateFileName(array $ids): string
    {
        if (count($ids) === 0) {
            throw new \Exception('Пустой перечень ПЛ для генерации имени файла');
        }

        if (count($ids) === 1) {
            return $this->generateSingleFileName(array_values($ids)[0]);
        }

        return $this->generateMultipleFileName($ids);
    }

    /**
     * @param EntityId $id
     * @return string
     * @throws \Exception
     */
    private function generateSingleFileName(EntityId $id): string
    {
        $tripTicket = TripTicket::query()->where('uuid', $id)->first();
        if (!$tripTicket) {
            throw new \Exception('ПЛ с таким UUID не найден');
        }

        $tripTicketType = $tripTicket->template_code;

        $tripTicketNumber = $tripTicket->ticket_number;

        $currentDate = date('YmdHis');

        return "PL_{$tripTicketType}_{$tripTicketNumber}_$currentDate.xlsx";
    }

    /**
     * @param EntityId[] $ids
     * @return string
     * @throws \Exception
     */
    private function generateMultipleFileName(array $ids): string
    {
        $tripTickets = TripTicket::query()
            ->select(['ticket_number'])
            ->whereIn('uuid', $ids)
            ->get();

        if ($tripTickets->count() === 0) {
            throw new \Exception('Не найдены ПЛ для генерации имени файла');
        }

        $trimmedTripTicketNumbers = substr(implode(",", $tripTickets->pluck('ticket_number')->toArray()), 0, 235);

        $currentDate = date('YmdHis');

        return "PL_{$trimmedTripTicketNumbers}_$currentDate.xlsx";
    }

    /**
     * @param EntityId[] $ids
     * @return Xlsx
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generate(array $ids): Xlsx
    {
        $templatePath = config('trip-ticket.print.template');
        $spreadsheet = $this->reader->load($templatePath);

        $this->validateTemplate($spreadsheet);

        $tripTickets = TripTicket::query()->whereIn('uuid', $ids)->get();

        foreach ($tripTickets as $index => $tripTicket) {
            $mapper = new ItemMapperStrategy($tripTicket);
            $writer = new SheetWriterStrategy(TripTicketTemplateEnum::fromString($tripTicket->template_code));

            $item = $mapper->map();
            $spreadsheet = $writer->createSheets($spreadsheet, $item, $index + 1);
        }

        // copy reverse sheets
        foreach ($this->reverseSheetNames() as $sheetName => $sheetPrefix) {
            $sheet = clone $spreadsheet->getSheetByName($sheetName);
            $sheet->setTitle($sheetPrefix);
            $spreadsheet->addSheet($sheet);
        }

        // delete template sheets
        foreach ($this->templateSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($sheet));
        }

        foreach ($tripTickets as $tripTicket) {
            event(new ChangeTripTicketStatus($tripTicket, TripTicketStatus::printed()));
        }

        return new Xlsx($spreadsheet);
    }

    private function validateTemplate(Spreadsheet $spreadsheet)
    {
        $sheets = $spreadsheet->getAllSheets();
        $titles = array_map(function (Worksheet $sheet) {
            return $sheet->getTitle();
        }, $sheets);

        foreach ($this->templateSheetNames() as $sheetName) {
            if (!in_array($sheetName, $titles)) {
                throw new DomainException("Template has no required sheet with name '$sheetName'");
            }
        }
    }

    private function reverseSheetNames(): array
    {
        return [
            config('trip-ticket.print.4s.template.reverse.sheet') => config('trip-ticket.print.4s.template.reverse.prefix')
        ];
    }

    private function templateSheetNames(): array
    {
        return [
            config('trip-ticket.print.4s.template.front.sheet'),
            config('trip-ticket.print.4s.template.reverse.sheet'),
        ];
    }
}
