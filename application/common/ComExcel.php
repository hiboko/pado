<?php

/**
 * ComExcel for class
 *
 * Excel生成クラス
 *
 * @category   ComExcel
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
final class ComExcel 
{
	/**
	 * Excel
	 */
	public $excel;

	/**
	 * シート
	 */
	public $sheet;

	/**
	 * ブック名
	 */
	public $book;

	/**
	* 共通処理
	*
	* @param   $templ   テンプレート場所
	* @param   $book    ブック名
	* @param   $title   シート名
	* @return
	*/
	public function __construct($templ, $book, $title)
	{
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$this->excel = $objReader->load($templ);
		$this->sheet = $this->excel->setActiveSheetIndex(0);
		$this->sheet->setTitle($title);
		$this->book = $book;
	}

	/**
	* シート追加
	*
	* @param   $title   シート名
	* @return
	*/
	public function AddSheet($title)
	{
		$sheet_copy = $this->excel->getSheet(0)->copy();
		$sheet_copy->setTitle($title);
		$this->excel->addSheet($sheet_copy);
		$this->sheet = $this->excel->getSheetByName($title);
	}

	/**
	* セル結合
	*
	* @param   $s_cell   対象開始セル
	* @param   $e_cell   対象終了セル
	* @return
	*/
	public function SetMergeCells($s_cell, $e_cell)
	{
		$this->sheet->mergeCells($s_cell . ':'. $e_cell);
	}

	/**
	* 行の高さ設定
	*
	* @param   $row   対象行
	* @param   $val   設定値
	* @return
	*/
	public function SetRowHeight($row, $val)
	{
		$this->sheet->getRowDimension($row)->setRowHeight($val);
	}

	/**
	* セル内横配置設定
	*
	* @param   $s_cell  対象開始セル
	* @param   $e_cell  対象終了セル
	* @param   $val     1:中央寄せ, 2:右寄せ
	* @return
	*/
	public function SetCellsAlign($s_cell, $e_cell, $val)
	{
		switch($val)
		{
			case 1:
				//中央寄せ
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				break;
			case 2:
				//右寄せ
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				break;
		}
	}

	/**
	* セル内縦配置設定
	*
	* @param   $s_cell  対象開始セル
	* @param   $e_cell  対象終了セル
	* @param   $val     1:上寄せ, 2:下寄せ, 3:中央寄せ
	* @return
	*/
	public function SetCellsVAlign($s_cell, $e_cell, $val)
	{
		switch($val)
		{
			case 1:
				//上寄せ
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				break;
			case 2:
				//下寄せ
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
				break;
			case 3:
				//中央寄せ
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				break;
		}
	}

	/**
	* 背景色設定
	*
	* @param   $s_cell  対象開始セル
	* @param   $e_cell  対象終了セル
	* @param   $color   色
	* @return
	*/
	public function SetCellsBackColor($s_cell, $e_cell, $color)
	{
		$this->sheet->getStyle($s_cell . ":" . $e_cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($color);
	}

	/**
	* フォント設定
	*
	* @param   $sCell  対象開始セル
	* @param   $eCell  対象終了セル
	* @param   $val    サイズ
	* @return
	*/
	public function SetCellsFontSize($s_cell, $e_cell, $bold, $size)
	{
		$this->sheet->getStyle($s_cell . ":" . $e_cell)->getFont()->setBold($bold)->setSize($size);
	}

	/**
	* 罫線処理
	*
	* @param   $s_cell   開始対象セル
	* @param   $e_cell   終了対象セル
	* @param   $type     種別(PHPExcel_Style_Border::BORDER_NONE（罫線なし）
	*                         PHPExcel_Style_Border::BORDER_THIN（通常）
	*                         PHPExcel_Style_Border::BORDER_MEDIUM（太線）
	*                         PHPExcel_Style_Border::BORDER_DASHED（破線）
	*                         PHPExcel_Style_Border::BORDER_DOTTED（点線）
	*                         PHPExcel_Style_Border::BORDER_THICK（太線2）
	*                         PHPExcel_Style_Border::BORDER_DOUBLE（二重線）
	*                         PHPExcel_Style_Border::BORDER_HAIR（細線）
	*                         PHPExcel_Style_Border::BORDER_MEDIUMDASHED（太線破線）
	*                         PHPExcel_Style_Border::BORDER_DASHDOT（一点鎖線）
	*                         PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT（太線の一点鎖線）
	*                         PHPExcel_Style_Border::BORDER_DASHDOTDOT（二点鎖線）
	*                         PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT（太線の二点鎖線）
	*                         PHPExcel_Style_Border::BORDER_SLANTDASHDOT（スラッシュ一点鎖線）
	* @param   $pflg  線引く場所(1:セル下側, 2:セル右側, 3:セル左側, 4:セル全て)
	* 
	* @return
	*/
	public function SetCellsLine($s_cell, $e_cell, $type, $pflg)
	{
		switch($pflg)
		{
			case 1:
				//セルの下側に罫線を引く
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getBorders()->getBottom()->setBorderStyle($type);
				break;
			case 2:
				//セルの右側に罫線を引く
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getBorders()->getLeft()->setBorderStyle($type);
				break;
			case 3:
				//セルの左側に罫線を引く
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getBorders()->getRight()->setBorderStyle($type);
				break;
			case 4:
				//セルの全てに罫線を引く
				$this->sheet->getStyle($s_cell . ":" . $e_cell)->getBorders()->getAllBorders()->setBorderStyle($type);
				break;
		}
	}

	/**
	* 縮小して全体を表示
	*
	* @param   $s_cell   開始対象セル
	* @param   $e_cell   終了対象セル
	* 
	* @return
	*/
	public function SetFitCellsFontSize($s_cell, $e_cell)
	{
		$this->sheet->getStyle($s_cell . ":" . $e_cell)->getAlignment()->setShrinkToFit(true);
	}

	/**
	* 折り返して全体を表示
	*
	* @param   $s_cell   開始対象セル
	* @param   $e_cell   終了対象セル
	* @param   $val      true:折り返す、false:折り返さない
	* 
	* @return
	*/
	public function SetCellsWrap($s_cell, $e_cell, $val)
	{
		$this->sheet->getStyle($s_cell . ":" . $e_cell)->getAlignment()->setWrapText($val);
	}

	/**
	* 数値フォーマット指定
	*
	* @param   $s_cell   開始対象セル
	* @param   $e_cell   終了対象セル
	* @param   $format   '#,##0','0.0%'
	* 
	* @return
	*/
	public function SetCellsNumberFormat($s_cell, $e_cell, $format)
	{
		$this->sheet->getStyle($s_cell . ":" . $e_cell)->getNumberFormat()->setFormatCode($format);
	}

	/**
	* データ設定
	*
	* @param   $arr   出力データ配列
	* @return
	*/
	public function SetCellValue($arr)
	{
		foreach ($arr as $key => $value)
		{
			$this->sheet->setCellValue($key, $value);
		}
	}

	/**
	* 文字列データ設定
	*
	* @param   $arr   出力データ配列
	* @return
	*/
	public function SetCellStringValue($arr)
	{
		foreach ($arr as $key => $value)
		{
			$this->sheet->setCellValueExplicit($key, $value, PHPExcel_Cell_DataType::TYPE_STRING);
		}
	}

	/**
	* 改ページ情報設定
	*
	* @param   $row   改ページ行セル
	* @param   $col   改ページカラムセル
	* @return
	*/
	public function SetBreakPage($row, $col)
	{
		$this->sheet->setBreak($row, PHPExcel_Worksheet::BREAK_ROW );
		$this->sheet->setBreak($col, PHPExcel_Worksheet::BREAK_COLUMN );
	}

	/**
	* 行削除
	*
	* @param   $sRow   削除対象開始行
	* @param   $eRow   削除対象終了行
	* @return
	*/
	public function RemoveRow($sRow, $eRow)
	{
		$this->sheet->removeRow($sRow, $eRow);

		// セル結合の解除
		foreach ($this->sheet->getMergeCells() as $mergeCell)
		{
			$mc = explode(":", $mergeCell);
			preg_match("/([A-Z]+)(\d+)/", $mc[0], $col_s);
			preg_match("/([A-Z]+)(\d+)/", $mc[1], $col_e);

			// 削除範囲内の場合
			if ($sRow <= $col_s[2] && $col_s[2] <= $eRow)
			{
				$this->sheet->unmergeCells($col_s[1] . $col_s[2] . ":" . $col_e[1] . $col_e[2]);
			}
		}
	}

	/**
	* データ範囲コピー
	*
	* @param   $sRow     複製元開始行番号
	* @param   $eRow     複製元終了行番号
	* @param   $width    複製カラム数
	* @param   $height   複製行数
	* @return
	*/
	public function CopyRows($sRow, $eRow, $width, $height)
	{
		$sheet = $this->sheet;

		for($col = 0; $col < $width; $col++)
		{
			for($row = $sRow; $row <= $eRow; $row++)
			{
				// セルを取得
				$cell = $sheet->getCellByColumnAndRow($col, $row);
				// セルスタイルを取得
				$style = $sheet->getStyleByColumnAndRow($col, $row);
				// 数値から列文字列に変換
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex($col) . (string)($row + $height);
				// セル値をコピー
				$sheet->setCellValue($offsetCell, $cell->getValue());
				// スタイルをコピー
				$sheet->duplicateStyle($style, $offsetCell);

				// 行の高さ複製
				$h = $sheet->getRowDimension($row)->getRowHeight();
				$sheet->getRowDimension($row + $height)->setRowHeight($h);
			}
		}

		// セル結合の複製
		foreach ($sheet->getMergeCells() as $mergeCell)
		{
			$mc = explode(":", $mergeCell);
			preg_match("/([A-Z]+)(\d+)/", $mc[0], $col_s);
			preg_match("/([A-Z]+)(\d+)/", $mc[1], $col_e);

			// 行範囲の場合
			if ($sRow <= $col_s[2] && $col_s[2] <= $eRow)
			{
				$sheet->mergeCells($col_s[1] . ($col_s[2] + $height) . ":" . $col_e[1] . ($col_e[2] + $height));
			}
		}
	}

	/**
	* Excel出力(2005形式)
	* 
	* @param 
	* @return
	*/
	public function OutputExcel()
	{
		header('Cache-Control: public');
		header('Pragma: public');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename="'. $this->book . '.xls"');
		$writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
		$writer->save('php://output');

		$this->excel->disconnectWorksheets();
    	unset($this->excel);
	}

	/**
	* Excel出力(2007形式)
	* 
	* @param 
	* @return
	*/
	public function OutputExcel2007()
	{
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'. $this->book . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
		$writer->save('php://output');

		$this->excel->disconnectWorksheets();
    	unset($this->excel);
	}
}

