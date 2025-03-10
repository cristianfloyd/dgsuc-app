<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:param name="owner" select="'SIU-Mapuche'"/>
<xsl:output method="html" encoding="iso-8859-1" indent="no"/>

<xsl:decimal-format name="european" decimal-separator="," grouping-separator="." />

<xsl:template match="recibos">
  ini_set('max_execution_time',0);
  			//Seteo las posiciones iniciales.

  			$posX = 35;
  			$posY = 14;

  			$posX_duplicado = $posX ;//+ 141;

  			$tamanio_largo_logo = 50;
  			$tamanio_ancho_logo = 19;


			$pdf=new PDF('P');

			//Tipo de fuente y tamaño.
			$pdf->SetFont('arial','',6);

			 <xsl:for-each select="cuerpo">
                            <xsl:call-template name="conceptos"/>
                        </xsl:for-each>

                        $pdf->Output();

</xsl:template>

<xsl:template name="A">
			$pdf->addPage();
			$pdf->SetMargins(0,0);

			//$pdf_dir_personalizado, se encuentra el path a los recursos personalizados.
			//Debe coincidir el nombre 'logo_personalizado.png' por el que se encuentra en la carpeta de las images de personalización.
			if(file_exists($pdf_dir_personalizado . '/soporte/images/recibologo.png')){
					//Seteo el logo de la institucion personalizado
					//$pdf->Image($pdf_dir_personalizado . '/soporte/images/logo_institucion.png',$posX,$posY,$tamanio_largo_logo,$tamanio_ancho_logo);
					$pdf->Image($pdf_dir_personalizado . '/soporte/images/recibologo.png',$posX,$posY,$tamanio_largo_logo,$tamanio_ancho_logo);
			} else {
					//Seteo el logo de la institucion
					$pdf->Image($pdf_dir . '/recursos/imagenes/logo_institucion.png',$posX,$posY,$tamanio_largo_logo,$tamanio_ancho_logo);
			}

			//Primero armo todo el cuadro de la factura.
			//ORIGINAL (dibujo el cuadro de la factura).

			//LINEAS HORIZONTALES.
			$pdf->Line($posX, $posY+19,$posX+137,$posY+19);
			$pdf->Line($posX, $posY+38,$posX+137,$posY+38);
			$pdf->Line($posX, $posY+40,$posX+137,$posY+40);
			$pdf->Line($posX, $posY+44,$posX+137,$posY+44);
			$pdf->Line($posX, $posY+51,$posX+137,$posY+51);
			$pdf->Line($posX, $posY+128,$posX+137,$posY+128);
			$pdf->Line($posX, $posY+134,$posX+137,$posY+134);
			$pdf->Line($posX, $posY+159,$posX+137,$posY+159);

			//Dibujo las dos lineas verticales
			$pdf->Line($posX, $posY+19,$posX,$posY+159);
			$pdf->Line($posX+137, $posY+19,$posX+137,$posY+159);
			$pdf->Line($posX+70, $posY+40,$posX+70,$posY+142);

			//Neto a Cobrar
			//Lineas Horizontales
			$pdf->Line($posX, $posY+142,$posX+137,$posY+142);
			$pdf->Line($posX, $posY+147,$posX+137,$posY+147);

			//Lineas Verticales
			//$pdf->Line($posX+70, $posY+136,$posX+70,$posY+142);
			//$pdf->Line($posX+137, $posY+136,$posX+137,$posY+142);

			//Cuadro abajo (Deposito Jubilacion)
			//Lineas Horizontales
			//$pdf->Line($posX_duplicado+67, $posY+143,$posX_duplicado+137,$posY+143);
			//$pdf->Line($posX_duplicado+67, $posY+163,$posX_duplicado+137,$posY+163);

			//Lineas Verticales
			//$pdf->Line($posX_duplicado+67, $posY+143,$posX_duplicado+67,$posY+163);
			//$pdf->Line($posX_duplicado+137, $posY+143,$posX_duplicado+137,$posY+163);

			//Datos comunes de la Factura (ORIGINAL-DUPLICADO)
			$pdf->setXY($posX+70,$posY);
			$encabezado = 'Recibo de Haberes: <xsl:value-of select="datos_legajo_liquidado/nro_recibo"/>';
			$pdf->WriteHTML($encabezado);

			$pdf->setXY($posX, $posY+12);
			$direccion_universidad = 'Dirección: <xsl:value-of select="../encabezado/direccion"/>';
			//$pdf->WriteHTML($direccion_universidad);

			$pdf->setXY($posX+70, $posY+6);
			$cuit_universidad = 'C.U.I.T UBA: <xsl:value-of select="../encabezado/cuit"/>';
			$pdf->WriteHTML($cuit_universidad);
			$pdf->setXY($posX_duplicado+70, $posY+6);
			$pdf->WriteHTML($cuit_universidad);

	/////////////////////Primer Cuadro///////////////////////////////////////////

			//En este primer cuadro se imprime:
				//	*	Periodo de Pago
				//	*	Días Trabajo
				//	*	Fecha de pago (fecha de emision??)

			$periodo_de_pago = 'Período: '.'<xsl:value-of select="../encabezado/desc_liqui"/>';
			$pdf->setXY($posX+60,$posY+22);
			$pdf->WriteHTML($periodo_de_pago);

			$dias_trabajo = 'Días Trabajo: '.'<xsl:value-of select="datos_legajo_liquidado/dias_trab"/>';
			$pdf->setX($posX+78);
			//$pdf->WriteHTML($dias_trabajo);

			$fecha = 'Fecha: '.'<xsl:value-of select="../encabezado/fec_emisi"/>';
			$pdf->setX($posX+108);
			//$pdf->WriteHTML($fecha);

	/////////////////////Segundo Cuadro/////////////////////////////////////////////
			//En este primer cuadro se imprime:
				//	*	Nombre y Apellido
				//	*	Numero Legajo ???
				//	*	Cantidad hs. por cargo (es lo mismo que hs. por cargo)
				//	*	CUIL-CUIT
				//	*	Regional
				//	*	Dependencia
				//	*	Categoria
				//	*	Dedicacion
				//	*	Fecha de Ingreso


			$nombre='Apellido y Nombres';
			$pdf->setXY($posX+2,$posY+26);
			//$pdf->WriteHTML($nombre);
			$dato_nombre='<xsl:value-of select="datos_legajo_liquidado/desc_apyno"/>';
			$pdf->setXY($posX+2,$posY+26);
			$pdf->WriteHTML($dato_nombre);

			$dato_legajo='Legajo: <xsl:value-of select="datos_legajo_liquidado/nro_legaj"/>';
			$pdf->setXY($posX+2,$posY+22);
			$pdf->WriteHTML($dato_legajo);

			$dato_hs_cargofiltra='<xsl:value-of select="datos_legajo_liquidado/hs_dedica"/>';

			$hs_cargo='Hs. por Cargo';
			if($dato_hs_cargofiltra==0){$hs_cargo='';}
			$pdf->setXY($posX+106,$posY+26);
			$pdf->WriteHTML($hs_cargo);

			$dato_hs_cargo='<xsl:value-of select="datos_legajo_liquidado/hs_dedica"/>';
			if($dato_hs_cargofiltra==0){$dato_hs_cargo='';}
			$pdf->setXY($posX+125,$posY+26);
			$pdf->WriteHTML($dato_hs_cargo);

			$cuil = '<xsl:value-of select="datos_legajo_liquidado/tipo_docum"/>'.' '.'<xsl:value-of select="datos_legajo_liquidado/nro_docum"/>';
			$pdf->setXY($posX+2,$posY+30);
			$pdf->WriteHTML($cuil);

			$dato_cuil='CUIL '.'<xsl:value-of select="datos_legajo_liquidado/nro_cuil1"/> - <xsl:value-of select="datos_legajo_liquidado/nro_cuil"/> - <xsl:value-of select="datos_legajo_liquidado/nro_cuil2"/>';
			$pdf->setXY($posX+20,$posY+30);
			$pdf->WriteHTML($dato_cuil);

			$regional = 'Regional';
			$pdf->setXY($posX+27,$posY+30);
			//$pdf->WriteHTML($regional);

			$dato_regional='<xsl:value-of select="datos_legajo_liquidado/codc_regio"/>';
			$pdf->setXY($posX+27,$posY+33);
			//$pdf->WriteHTML($dato_regional);

			$dependencia = 'Dependencia';
			$pdf->setXY($posX+44,$posY+30);
			//$pdf->WriteHTML($dependencia);

			$dato_dependencia='<xsl:value-of select="datos_legajo_liquidado/desc_depcia"/>';
			$pdf->setXY($posX+60,$posY+30);
			$pdf->WriteHTML($dato_dependencia);

			$categoria = 'Categoria';
			$pdf->setXY($posX+65,$posY+30);
			//$pdf->WriteHTML($categoria);

			//$dato_categoria='<xsl:value-of select="datos_legajo_liquidado/codc_categ"/>';
			$dato_categoria='<xsl:value-of select="datos_legajo_liquidado/nro_cargo"/>'.'-'.'<xsl:value-of select="datos_legajo_liquidado/desc_categ"/>'."-".'<xsl:value-of select="datos_legajo_liquidado/desc_dedic"/>';
			$pdf->setXY($posX+60,$posY+33);
			$pdf->WriteHTML($dato_categoria);

			$dedicacion = 'Dedicacion';
			$pdf->setXY($posX+83,$posY+30);
			//$pdf->WriteHTML($dedicacion);

			$dato_dedicacion='<xsl:value-of select="datos_legajo_liquidado/codc_dedic"/>';
			$pdf->setXY($posX+83,$posY+33);
			//$pdf->WriteHTML($dato_dedicacion);

			$ingreso = 'F. Ingreso';
			$pdf->setXY($posX+103,$posY+30);
			$pdf->WriteHTML($ingreso);

			$dato_ingreso='<xsl:value-of select="datos_legajo_liquidado/fec_ingreso"/>';
			$pdf->setXY($posX+103,$posY+33);
			$pdf->WriteHTML($dato_ingreso);

	/////////////////////Tercer Cuadro/////////////////////////////////////////////

			$haberes = 'Haberes';
			$pdf->setXY($posX+25,$posY+40);
			$pdf->WriteHTML($haberes);

			$pdf->setXY($posX+85,$posY+40);
			$retenciones = 'Retenciones';
			$pdf->WriteHTML($retenciones);

/////////////////////Cuarto Cuadro/////////////////////////////////////////////

			$pdf->setXY($posX+7,$posY+46);
			$descripcion = 'Descripción';
			$pdf->WriteHTML($descripcion);
			$pdf->setX($posX+78);
			$pdf->WriteHTML($descripcion);

			$pdf->setXY($posX+56,$posY+46);
			$importe = 'Importe';
			$pdf->WriteHTML($importe);
			$pdf->setX($posX+124);
			$pdf->WriteHTML($importe);
</xsl:template>


<xsl:template name="B">
	<xsl:param name="pos_H" />
	<xsl:param name="pos_R" />
	<xsl:param name="nodo" />

	<xsl:variable name="upper">ABCDEFGHIJKLMNOPQRSTUVWXYZÜ</xsl:variable>
	<xsl:variable name="lower">abcdefghijklmnopqrstuvwxyzü</xsl:variable>


	<xsl:variable name="nro_concepto">
		<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/nro_conce" />
	</xsl:variable>

	<xsl:variable name="desc_concepto">
		<xsl:value-of select="translate(concepto_a_aplicar/concepto[position()=$nodo]/desc_conc,$lower,$upper)" />
	</xsl:variable>

	<xsl:variable name="importe_concepto">
		<xsl:value-of select="format-number(concepto_a_aplicar/concepto[position()=$nodo]/impo_conc, '###.##0,00', 'european')"/>
	</xsl:variable>

	<xsl:variable name="novedad1_concepto">
		<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/novedad1"/>
	</xsl:variable>

	<xsl:variable name="hb_concepto">
		<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/codc_hhdd"/>
	</xsl:variable>

	<xsl:variable name="dias_cargo_trabajado">
		<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/dias_cargo_trabajado"/>
	</xsl:variable>

	<xsl:variable name="mes_retro">
		<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/mes_retro"/>
	</xsl:variable>

	<xsl:variable name="ano_retro">
		<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/ano_retro"/>
	</xsl:variable>

	<xsl:variable name="observa">
		<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/observa"/>
	</xsl:variable>

	<xsl:variable name="remanente">
		<xsl:choose>
  				<xsl:when test="concepto_a_aplicar/concepto[position()=$nodo]/remanente != 000">
  					<xsl:value-of select="concepto_a_aplicar/concepto[position()=$nodo]/remanente"/>
  				</xsl:when>
  				<xsl:otherwise>
  					<xsl:value-of select="0"/>
  				</xsl:otherwise>
  		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="tipo_concepto">
		<xsl:choose>
  			<xsl:when test="concepto_a_aplicar/concepto[position()=$nodo]/tipo_conce = 'RB'">
  				A
  			</xsl:when>
  			<xsl:when test="concepto_a_aplicar/concepto[position()=$nodo]/tipo_conce = 'NRB'">
  				B
  			</xsl:when>
  			<xsl:when test="concepto_a_aplicar/concepto[position()=$nodo]/tipo_conce = 'RNB'">
  				C
  			</xsl:when>
  			<xsl:when test="concepto_a_aplicar/concepto[position()=$nodo]/tipo_conce = 'NRNB'">
  				D
  			</xsl:when>
  		</xsl:choose>

	</xsl:variable>

	<xsl:if test="$hb_concepto='H'">
			<xsl:choose>
  				<xsl:when test="$importe_concepto='0,00'">
  					$haberes = '<b><xsl:value-of select="$desc_concepto"/></b>' . ' <xsl:value-of select="$dias_cargo_trabajado"/> (<xsl:value-of select="$remanente"/>)';
  					$pdf->setXY($posX+1,$posY+48+(<xsl:value-of select="$pos_H"/>*3));
					$pdf->WriteHTML($haberes);
				</xsl:when>
  				<xsl:otherwise>
  					$haberes = '<xsl:value-of select="$nro_concepto"/>-<xsl:value-of select="$desc_concepto"/>';
  					$pdf->setXY($posX+1,$posY+48+(<xsl:value-of select="$pos_H"/>*3));
					$pdf->WriteHTML($haberes);
					<xsl:if test="$desc_concepto='ANTIGÜEDAD'">
						$haberes='Años: <xsl:value-of select="$novedad1_concepto"/>';
						$pdf->setX($posX+35);
  						$pdf->WriteHTML($haberes);
  					</xsl:if>
					$haberes = '';
					<xsl:if test="$mes_retro!='0' and $ano_retro!='0'">
						$haberes='<xsl:value-of select="$mes_retro"/>/<xsl:value-of select="$ano_retro"/>';

  					</xsl:if>

					$haberes = '';
		            <xsl:if test = "$mes_retro!='0' and $ano_retro!='0'">
		            	$haberes = '<xsl:value-of select="$mes_retro"/>/<xsl:value-of select="$ano_retro"/>';
		            </xsl:if>
		            <xsl:if test="not(contains($observa,concat($dias_cargo_trabajado,$remanente)))">
		            	$haberes .= ' <xsl:value-of select="$observa"/>';
		            </xsl:if>
		            $pdf->setX($posX+45);
					$pdf->WriteHTML($haberes);

					$importe='<xsl:value-of select="$importe_concepto"/>';
					$pdf->setXY($posX+56,$posY+51+(<xsl:value-of select="$pos_H"/>*3));
					$pdf->Cell(12,0,$importe,0,0,'R');

					$pdf->setXY($posX+62,$posY+51+(<xsl:value-of select="$pos_H"/>*3));
					$pdf->Cell(12,0,'<xsl:value-of select="$tipo_concepto"/>',0,0,'R');

				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
		<xsl:if test="$hb_concepto='R'">
  			$retenciones = '<xsl:value-of select="$nro_concepto"/>-<xsl:value-of select="$desc_concepto"/>';
  			$pdf->setXY($posX+70,$posY+48+(<xsl:value-of select="$pos_R"/>*3));
			$pdf->WriteHTML($retenciones);

			$retenciones = '';
            <xsl:if test = "$mes_retro!='0' and $ano_retro!='0'">
            	$retenciones = '<xsl:value-of select="$mes_retro"/>/<xsl:value-of select="$ano_retro"/>';
            </xsl:if>
            <xsl:if test="not(contains($observa,concat($dias_cargo_trabajado,$remanente)))">
            	$retenciones .= ' <xsl:value-of select="$observa"/>';
            </xsl:if>
            $pdf->setX($posX+120);
			$pdf->WriteHTML($retenciones);

			$importe='<xsl:value-of select="$importe_concepto"/>';
			$pdf->setXY($posX+124,$posY+51+(<xsl:value-of select="$pos_R"/>*3));
			$pdf->Cell(12,0,$importe,0,0,'R');
		</xsl:if>
</xsl:template>

<xsl:template name="C">
	<xsl:param name="hoja" />
	<xsl:param name="total" />
	<xsl:param name="subtotal_haberes" />
	<xsl:param name="subtotal_retenciones" />

			//$total = 'Total hasta hoja 			<xsl:value-of select="$hoja"/>';
			$total = 'Total Haberes ';
			$pdf->setXY($posX+1,$posY+130);
			$pdf->WriteHTML($total);
			$total = 'Total Descuentos ';
			$pdf->setX($posX+73);
			$pdf->WriteHTML($total);

			$importe = '<xsl:value-of select="format-number($subtotal_haberes, '###.##0,00', 'european')"/>';
			$pdf->setX($posX+56);
			$pdf->WriteHTML($importe);
			$importe = '<xsl:value-of select="format-number($subtotal_retenciones, '###.##0,00', 'european')"/>';
			$pdf->setX($posX+126);
			$pdf->WriteHTML($importe);

			$neto= 'Neto a Cobrar:';
			$pdf->setXY($posX+74,$posY+137);
			$pdf->WriteHTML($neto);

			$importe = '*** $ '.'<xsl:value-of select="format-number(datos_legajo_liquidado/tot_neto, '###.##0,00', 'european')"/>';
			$pdf->setXY($posX+115,$posY+137);
			$pdf->WriteHTML($importe);

	/////////////////////Datos Solo Duplicado/////////////////////////////////////////////

			//obra social
			$obra_social='<xsl:value-of select="datos_legajo_liquidado/obrasocial"/>';
			$pdf->setXY($posX_duplicado+2,$posY+143);
			//$pdf->WriteHTML($obra_social);


			<xsl:if test="datos_legajo_liquidado/tipocuenta !=''">
				$acreditado_en = 'Acreditado en <xsl:value-of select="datos_legajo_liquidado/tipocuenta"/>';
				$pdf->setXY($posX_duplicado+2,$posY+143);
				//$pdf->WriteHTML($acreditado_en);
				$acreditado_en = 'Nro. <xsl:value-of select="datos_legajo_liquidado/ctabanco"/>';
				$pdf->setXY($posX_duplicado+2,$posY+150);
				//$pdf->WriteHTML($acreditado_en);
				$acreditado_en = 'Sueldo acreditado en <xsl:value-of select="datos_legajo_liquidado/entidad_bancaria"/>';
				$pdf->setXY($posX_duplicado+2,$posY+143);
				$pdf->WriteHTML($acreditado_en);
			</xsl:if>

			$leyenda = '<xsl:value-of select="datos_legajo_liquidado/texto1"/>';
			$pdf->setXY($posX_duplicado+60,$posY+26);
			$pdf->WriteHTML($leyenda);
			$leyenda = '<xsl:value-of select="datos_legajo_liquidado/texto2"/>';
			$pdf->setXY($posX_duplicado+2,$posY+160);
			$pdf->WriteHTML($leyenda);
			$leyenda = '<xsl:value-of select="datos_legajo_liquidado/texto3"/>';
			$pdf->setXY($posX_duplicado+2,$posY+162);
			$pdf->WriteHTML($leyenda);
			$leyenda = '<xsl:value-of select="datos_legajo_liquidado/texto4"/>';
			$pdf->setXY($posX_duplicado+2,$posY+164);
			$pdf->WriteHTML($leyenda);

			//Deposito jubilación
			$titulo='Aportes y contribuciones';
			$pdf->setXY($posX_duplicado+2,$posY+148);
			$pdf->WriteHTML($titulo);

			$fecha='Fecha deposito : <xsl:value-of select="../encabezado/fec_ultap"/>';
			$pdf->setXY($posX_duplicado+25,$posY+152);
			$pdf->WriteHTML($fecha);

			//Ver si el período mes y el periodo año estan bien o son lo de los aportes patronales
			$periodo='Periodo: <xsl:value-of select="../encabezado/per_mesap"/> / <xsl:value-of select="../encabezado/per_anoap"/>';
			$pdf->setXY($posX_duplicado+2,$posY+152);
			$pdf->WriteHTML($periodo);

			$banco='Banco: <xsl:value-of select="../encabezado/desc_lugap"/>';
			$pdf->setXY($posX_duplicado+55,$posY+152);
			$pdf->WriteHTML($banco);

			//Firma
			$firma = 'Firma Responsable';
			$pdf->setXY($posX_duplicado+93,$posY+170);
			//$pdf->WriteHTML($firma);
			//$pdf->Line($posX_duplicado+70, $posY+171,$posX_duplicado+135,$posY+171);



</xsl:template>

<xsl:template name="D">
		<xsl:param name="hoja" />

			<xsl:variable name="tipo_rb">
            	 <xsl:value-of select="format-number(sum(concepto_a_aplicar/concepto[tipo_conce='RB' and nro_renglo &lt; $hoja*26]/impo_conc), '###.##0,00', 'european') "/>
       		</xsl:variable>


			<xsl:variable name="tipo_nrb">
				<xsl:value-of select="format-number(sum(concepto_a_aplicar/concepto[tipo_conce='NRB' and nro_renglo &lt; $hoja*26]/impo_conc), '###.##0,00', 'european') "/>
			</xsl:variable>

			<xsl:variable name="tipo_rnb">
				<xsl:value-of select="format-number(sum(concepto_a_aplicar/concepto[tipo_conce='RNB' and nro_renglo &lt; $hoja*26]/impo_conc), '###.##0,00', 'european') "/>
			</xsl:variable>

			<xsl:variable name="tipo_nrnb">
				<xsl:value-of select="format-number(sum(concepto_a_aplicar/concepto[tipo_conce='NRNB' and nro_renglo &lt; $hoja*26]/impo_conc), '###.##0,00', 'european') "/>
			</xsl:variable>

		$valores = '(A) RB';
		$pdf->setXY($posX+5,$posY+135);
		//$pdf->WriteHTML($valores);

		$valor_RB = '<xsl:value-of select="$tipo_rb"/>';
		$pdf->setXY($posX+30,$posY+135);
		//$pdf->WriteHTML($valor_RB);

		$valores = '(C) RNB ';
		$pdf->setXY($posX+5,$posY+139);
		//$pdf->WriteHTML($valores);

		$valor_RNB = '<xsl:value-of select="$tipo_rnb"/>';
		$pdf->setXY($posX+30,$posY+139);
		//$pdf->WriteHTML($valor_RNB);

		$valores = '(B) NRB';
		$pdf->setXY($posX+39,$posY+135);
		//$pdf->WriteHTML($valores);

		$valor_NRB = '<xsl:value-of select="$tipo_nrb"/>';
		$pdf->setXY($posX+60,$posY+135);
		//$pdf->WriteHTML($valor_NRB);

		$valores = '(D) NRNB';
		$pdf->setXY($posX+39,$posY+139);
		//$pdf->WriteHTML($valores);

		$valor_NRNB = '<xsl:value-of select="$tipo_nrnb"/>';
		$pdf->setXY($posX+60,$posY+139);
		//$pdf->WriteHTML($valor_NRNB);

</xsl:template>

<xsl:template name="conceptos">
		<xsl:call-template name="concepto_renglones">
			<xsl:with-param name="pos_H">0</xsl:with-param>
			<xsl:with-param name="pos_R">0</xsl:with-param>
         	<xsl:with-param name="hoja">0</xsl:with-param>
         	<xsl:with-param name="nodo">0</xsl:with-param>
         	<xsl:with-param name="subtotal_haberes">0</xsl:with-param>
         	<xsl:with-param name="subtotal_retenciones">0</xsl:with-param>
         	<xsl:with-param name="cnt_nodos"><xsl:value-of select="count(concepto_a_aplicar/concepto)"/></xsl:with-param>
         	<xsl:with-param name="tipo_concepto"><xsl:value-of select="concepto_a_aplicar/concepto/codc_hhdd"/></xsl:with-param>
         	<xsl:with-param name="cambio">0</xsl:with-param>
		</xsl:call-template>
</xsl:template>

<xsl:template name="concepto_renglones">
	<xsl:param name="pos_H" />
	<xsl:param name="pos_R" />
	<xsl:param name="nodo" />
	<xsl:param name="hoja" />
	<xsl:param name="cnt_nodos" />
	<xsl:param name="subtotal_haberes" />
	<xsl:param name="subtotal_retenciones" />
	<xsl:param name="cambio" />

	<xsl:variable name="tipo">
		<xsl:value-of select="concepto_a_aplicar/concepto[$nodo+1]/codc_hhdd "/>
	</xsl:variable>
	<xsl:choose>
		<xsl:when test=" $cambio=0" >
  			<xsl:call-template name="A"/>
  			<xsl:call-template name="concepto_renglones">
         			<xsl:with-param name="pos_H"><xsl:value-of select="$pos_H "/></xsl:with-param>
         			<xsl:with-param name="pos_R"><xsl:value-of select="$pos_R"/></xsl:with-param>
         			<xsl:with-param name="hoja"><xsl:value-of select="$hoja + 1"/></xsl:with-param>
         			<xsl:with-param name="nodo"><xsl:value-of select="$nodo"/></xsl:with-param>
         			<xsl:with-param name="cnt_nodos"><xsl:value-of select="$cnt_nodos"/></xsl:with-param>
         			<xsl:with-param name="subtotal_haberes"><xsl:value-of select="$subtotal_haberes"/></xsl:with-param>
         			<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones"/></xsl:with-param>
         			<xsl:with-param name="cambio">1</xsl:with-param>
         	</xsl:call-template>
		</xsl:when>
		<xsl:when test="$pos_H=26 and $tipo='H'" >
			<xsl:call-template name="D">
				<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="C">
					<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
					<xsl:with-param name="subtotal_haberes"><xsl:value-of select="$subtotal_haberes - number(concepto_a_aplicar/concepto[position()=$nodo]/impo_conc)"/></xsl:with-param>
					<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones"/></xsl:with-param>
  			</xsl:call-template>
			<xsl:call-template name="concepto_renglones">
         			<xsl:with-param name="pos_H">1</xsl:with-param>
         			<xsl:with-param name="pos_R"><xsl:value-of select="$pos_R"/></xsl:with-param>
         			<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
         			<xsl:with-param name="nodo"><xsl:value-of select="$nodo"/></xsl:with-param>
         			<xsl:with-param name="cnt_nodos"><xsl:value-of select="$cnt_nodos"/></xsl:with-param>
         			<xsl:with-param name="subtotal_haberes"><xsl:value-of select="$subtotal_haberes"/></xsl:with-param>
         			<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones"/></xsl:with-param>
         			<xsl:with-param name="cambio">0</xsl:with-param>
         	</xsl:call-template>
		</xsl:when>
		<xsl:when test="$pos_R=26 and $tipo='R'" >
		<xsl:call-template name="D">
				<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="C">
					<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
					<xsl:with-param name="subtotal_haberes"><xsl:value-of select="$subtotal_haberes"/></xsl:with-param>
					<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones"/></xsl:with-param>
  			</xsl:call-template>
			<xsl:call-template name="concepto_renglones">
         			<xsl:with-param name="pos_R">1</xsl:with-param>
         			<xsl:with-param name="pos_H"><xsl:value-of select="$pos_H"/></xsl:with-param>
         			<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
         			<xsl:with-param name="nodo"><xsl:value-of select="$nodo"/></xsl:with-param>
         			<xsl:with-param name="cnt_nodos"><xsl:value-of select="$cnt_nodos"/></xsl:with-param>
         			<xsl:with-param name="subtotal_haberes"><xsl:value-of select="$subtotal_haberes"/></xsl:with-param>
         			<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones"/></xsl:with-param>
         			<xsl:with-param name="cambio">0</xsl:with-param>
         	</xsl:call-template>
		</xsl:when>
		<xsl:when test="$nodo=$cnt_nodos" >
			<xsl:call-template name="B">
						<xsl:with-param name="pos_H"><xsl:value-of select="$pos_H"/></xsl:with-param>
  						<xsl:with-param name="pos_R"><xsl:value-of select="$pos_R"/></xsl:with-param>
  						<xsl:with-param name="nodo"><xsl:value-of select="$nodo"/></xsl:with-param>
  				</xsl:call-template>
			<xsl:call-template name="C">
					<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
					<xsl:with-param name="subtotal_haberes"><xsl:value-of select="$subtotal_haberes"/></xsl:with-param>
					<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones"/></xsl:with-param>
  			</xsl:call-template>
  			<xsl:call-template name="D">
				<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
				<xsl:call-template name="B">
						<xsl:with-param name="pos_H"><xsl:value-of select="$pos_H"/></xsl:with-param>
  						<xsl:with-param name="pos_R"><xsl:value-of select="$pos_R"/></xsl:with-param>
  						<xsl:with-param name="nodo"><xsl:value-of select="$nodo"/></xsl:with-param>
  				</xsl:call-template>
				<xsl:choose>
					<xsl:when test="$tipo='H'" >
  						<xsl:call-template name="concepto_renglones">
         					<xsl:with-param name="pos_H"><xsl:value-of select="$pos_H + 1"/></xsl:with-param>
         					<xsl:with-param name="pos_R"><xsl:value-of select="$pos_R"/></xsl:with-param>
         					<xsl:with-param name="nodo"><xsl:value-of select="$nodo+1"/></xsl:with-param>
         					<xsl:with-param name="cnt_nodos"><xsl:value-of select="$cnt_nodos"/></xsl:with-param>
         					<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
         					<xsl:with-param name="subtotal_haberes"><xsl:value-of select=" $subtotal_haberes + number(concepto_a_aplicar/concepto[$nodo+1]/impo_conc) "/></xsl:with-param>
         					<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones"/></xsl:with-param>
         					<xsl:with-param name="cambio"><xsl:value-of select="$cambio"/></xsl:with-param>
         				</xsl:call-template>
         			</xsl:when>
         			<xsl:otherwise>
  						<xsl:call-template name="concepto_renglones">
         					<xsl:with-param name="pos_H"><xsl:value-of select="$pos_H"/></xsl:with-param>
         					<xsl:with-param name="pos_R"><xsl:value-of select="$pos_R + 1"/></xsl:with-param>
         					<xsl:with-param name="nodo"><xsl:value-of select="$nodo+1"/></xsl:with-param>
         					<xsl:with-param name="cnt_nodos"><xsl:value-of select="$cnt_nodos"/></xsl:with-param>
         					<xsl:with-param name="hoja"><xsl:value-of select="$hoja"/></xsl:with-param>
         					<xsl:with-param name="subtotal_haberes"><xsl:value-of select="$subtotal_haberes"/></xsl:with-param>
         					<xsl:with-param name="subtotal_retenciones"><xsl:value-of select="$subtotal_retenciones + number(concepto_a_aplicar/concepto[$nodo+1]/impo_conc)"/></xsl:with-param>
         					<xsl:with-param name="cambio"><xsl:value-of select="$cambio"/></xsl:with-param>
         				</xsl:call-template>
					</xsl:otherwise>
			</xsl:choose>
        </xsl:otherwise>
	</xsl:choose>
</xsl:template>
</xsl:stylesheet>
