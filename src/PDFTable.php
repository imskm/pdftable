<?php

namespace PDFTable;

use FPDF;
use PDFTable\PDFTableColumn;

/**
 * PDFTable
 */
class PDFTable extends FPDF
{
	public $font = 'helvetica';                 /* Font Name : See inc/fpdf/font for all supported fonts */
	public $margins = [
        'l' => 15,
        't' => 15,
        'r' => 15,
    ]; /* l: Left Side , t: Top Side , r: Right Side */

    public $body_text_lineheight = 4;
    public $col_gap = 2;

	public $document;
	private $logo;
	private $brand_color;

	private $cols = [];
	private $rows = [];
	private $reference;
	private $bill_date;
	private $bill_from; // ['company_name', 'company_address_line_1', 'company_address_line_2', ...]
	private $bill_to;   // ['party_name', 'party_address_line_1', 'party_address_line_2', ...]

	private $columns;

	public function __construct()
	{
		$this->document['w'] = 210;
		$this->document['h'] = 297;

		parent::__construct('P', 'mm');
	    $this->SetMargins($this->margins['l'], $this->margins['t'], $this->margins['r']);
	    $this->SetFillColor(0, 0, 0);
	}

	public function setLogo($logo_filepath)
	{
		$this->logo = $logo_filepath;
	}

	public function setFrom(array $from)
	{
		$this->bill_from = $from;
	}

	public function setColor($color)
	{
		$this->brand_color = $color;
	}

	private function buildHeader()
	{
		$logo_w = 24.0;
		$logo_h = 20.0;
		$lineheight = $this->body_text_lineheight;
		$start_position_x = $this->margins['l'];
		// Logo
		$this->Image($this->logo, $this->margins['l'], $this->margins['t'], $logo_w, $logo_h);


		// Move the position
		$from_addr_position_x = $this->margins['l'] + $logo_w;
		$this->Cell($from_addr_position_x, $lineheight);

		// Company name
		$this->SetFont($this->font, "B", 12);
		$title = array_shift($this->bill_from);
		$this->Cell(0, $lineheight, $title, 0, 1, "L");

		// From address
		$this->SetFont($this->font, "", 8);
		$this->Ln(2);
		foreach ($this->bill_from as $line) {
			$this->Cell($from_addr_position_x, $lineheight);
			$this->Cell(0, $lineheight, $line, 0, 1, "L");
		}
		$this->Ln($lineheight * 2);

		$this->buildTableHeader();
	}

	private function buildTableHeader()
	{
		// Draw a line
		$x1 = 0;
		$y1 = $this->getY();
		$x2 = $this->document['w'];
		$y2 = $y1;
		$thickness = 0.3;
		$this->SetLineWidth($thickness);
		// @NOTE Drawing line brings the cursor position to down left margin
		$this->Line($x1, $y1, $x2, $y2);

		$first_col_width = 5; // 5 mm = 1/2 cm
		$start_position_x = $this->margins['l'];
		$lineheight = $this->body_text_lineheight * 2;

		$col_width = $this->calculateColumnWidth();
		$total_cols = count($this->columns);
		$this->SetFont($this->font, "B");
		foreach ($this->columns as $i => $col) {
			// Bring down the Y position to bottom if this is the last column
			if ($i === $total_cols - 1) {
				$this->Cell($col_width, $lineheight, $col->name, 0, 1, "L");
			} else {
				$this->Cell($col_width, $lineheight, $col->name, 0, 0, "L");
			}
			// column gap
			if ($i !== $total_cols - 1) {
				$this->Cell($this->col_gap, $lineheight);
			}
		}
		$y1 = $this->getY();
		$y2 = $y1;
		$this->Line($x1, $y1, $x2, $y2);

	}

	private function calculateColumnWidth()
	{
		$total_cols = count($this->columns);
		$page_width = $this->document['w'] - $this->margins['l'] - $this->margins['r'] - $this->col_gap * ($total_cols - 1);

		return $page_width / $total_cols;
	}

	private function buildBody()
	{
		$lineheight = $this->body_text_lineheight * 1.5;
		$total_cols = count($this->columns);
		$col_width = $this->calculateColumnWidth();
		$this->SetFont($this->font, "");
		$this->Ln($lineheight / 2);
		$this->SetLineWidth(0.2);
		foreach ($this->rows as $i => $row) {
			foreach ($row as $j => $value) {
				if ($i === 0 && $j === 0)
					$border = "BTL";
				else if ($i === 0 && $j !== $total_cols - 1)
					$border = "BT";
				else if ($i === 0 && $j === $total_cols - 1)
					$border = "BTR";
				else if ($j === 0 && $i !== 0)
					$border = "BL";
				else if ($j === $total_cols - 1 && $i !== 0)
					$border = "BR";
				else
					$border = "B";

				// Bring down the Y position to bottom if this is the last column
				if ($j === $total_cols - 1) {
					$this->Cell($col_width, $lineheight, $value, $border, 1, "L");
				} else {
					$this->Cell($col_width, $lineheight, $value, $border, 0, "L");
				}
				// column gap
				if ($j !== $total_cols - 1) {
					$this->Cell($this->col_gap, $lineheight, "", $i === 0 ? "BT" : "B");
				}
			}
		}
	}

	private function buildFooter()
	{
		// 
	}


	private function buildPDF()
	{
		$this->AddPage();
		$this->buildHeader();
		$this->buildBody();
		$this->buildFooter();
	}

	public function render($filename, $type)
	{
		$this->buildPDF();
		$this->Output($filename, $type);
	}

	public function addColumn($name)
	{
		$this->columns[] = new PDFTableColumn($name);

		return $this;
	}

	public function addRecord(array $item)
	{
		if (count($item) !== count($this->columns)) {
			throw new \Exception("Number of columns and number of fields in a row missmatch");
		}

		$this->rows[] = $item;
	}
}
