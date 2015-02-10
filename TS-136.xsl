<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- Útgáfa 1.73 BII, 2. júlí 2014 -->
<!-- Þorkell Pétursson, thorkell.petursson@fjs.is / thorkellp@gmail.com -->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:clm54217="urn:un:unece:uncefact:codelist:specification:54217:2001" xmlns:clm5639="urn:un:unece:uncefact:codelist:specification:5639:1988" xmlns:clm66411="urn:un:unece:uncefact:codelist:specification:66411:2001" xmlns:clmIANAMIMEMediaType="urn:un:unece:uncefact:codelist:specification:IANAMIMEMediaType:2003" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:n1="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xdt="http://www.w3.org/2005/xpath-datatypes" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
	<xsl:output version="4.0" method="html" indent="no" encoding="ISO-8859-1" media-type="text/html" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>
	<xsl:decimal-format name="IcelandicNumber" decimal-separator="," grouping-separator="."/>
	<xsl:variable name="erpro">
		<xsl:call-template name="ProsentaHeader"/>
	</xsl:variable>
	<xsl:variable name="ergrunnupph">
		<xsl:call-template name="GrunnupphaedHeader"/>
	</xsl:variable>
	<xsl:template match="n1:Invoice">
		<html>
			<head>
				<link href="style.css" title="default" type="text/css" media="screen,projection,print" rel="stylesheet"/>
				<script src="toggleContainer.js" type="text/javascript"/>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
				<title>Reikningur&#160;-&#160;<xsl:value-of select="cbc:ID"/></title>
			</head>
			<body>
				<xsl:call-template name="Sida"/>
				<xsl:call-template name="Annad"/>
				<div style="margin-top: 2em;"/>
			</body>
		</html>
	</xsl:template>
	
	<!-- Forsíða -->
	<xsl:template name="Sida">
		<div id="sida" class="sida">
					<div id="haus">
						<div id="hausseljandi">
							<!-- seljandi -->
							<div id="seljandi" class="leftseljandi">
								<xsl:call-template name="Seljandi"/>
							</div>
							<!-- haus reikningur -->
							<div id="hausreikningur" class="righthausreikningur">
								<xsl:call-template name="Reikningur"/>
							</div>
							
						</div>
						<!-- hausefri -->
						<div id="hausefri">
							<!-- Greiðandi -->
							<div id="greidandi" class="leftgreidandi">
								<xsl:call-template name="Greidandi"/>
							</div>
							
							<!-- haussummur -->
							<div id="haussummur" class="righthaussummur">
								<xsl:call-template name="Summur"/>
							</div>
							
						</div>
						<!-- hausnedri -->
						<div id="hausnedri" class="floatsamantekt">
							<!-- Greiðslu upplýsingar -->
							<div id="greidsluupplysingar" class="leftgreidsluupplysingar">
								<xsl:call-template name="Greidsluupplysingar"/>
							</div>
							
							<!-- Lýsing hægra megin-->
							<div id="lysing" class="rightlysing">
								<xsl:call-template name="Lysing"/>
							</div>
						
						</div><p class="clear" />
						
					<!-- Línur reiknings -->
					<xsl:call-template name="Linur"/>

					<!-- Skattar / Samtölur -->
					<div class="floatsamantekt">
					<xsl:call-template name="SkattarOgAfslnytt"/>					
					<xsl:call-template name="Samtolur"/>
					</div><p class="clear" />
				</div>
		</div>
		
	</xsl:template>
	
	<!-- Undirsíða. Þarf Javascript til að virka. -->
	<xsl:template name="Annad">
		<div id="sidaannad" class="annadsidafalid">
			
			<!-- Javascript takki. -->
			<div class="ntable2">
				<div onclick="toggleContainer('annad', 'annadopencllink', 'Sýna meira', 'Fela', 'closedlink', 'openlink', 'sidaannad', 'annadsidafalid', 'annadsida');" class="openlink" id="annadopencllink">Sýna meira</div>
			</div>
			
			<div id="annad" class="annadfela">
				<!-- Afhendingarupplýsingar -->
				<xsl:call-template name="AnnadAfhendingarstadur"/>
				
				<!-- Tengiliðir seljanda / kaupanda -->
				<div class="floatkassi">
					<xsl:call-template name="TengilidurSeljanda"/>
					<xsl:call-template name="TengilidurKaupanda"/>
				</div><p class="clear" />
				
				<!-- Ítarupplýsingar um afslætti og gjöld á haus -->
				<xsl:call-template name="AnnadItarupplLinuAflshaus"/>
				
				<!-- Ítarupplýsingar á línum -->
				<xsl:call-template name="AnnadItarupplLinu"/>
				
				<!-- Ítarupplýsingar um afslátt eða gjald á einingarverð -->
				<xsl:call-template name="AnnadItarupplLinuAfls"/>
				
				<!-- Ítarupplýsingar um afslætti og gjöld á línum -->
				<xsl:call-template name="AnnadItarupplLinuAfls2"/>
				
				<!-- Viðhengi -->
				<xsl:call-template name="AnnadVidhengi2"/>
			</div>	
		</div>
	</xsl:template>
	
<!-- Template sem kallað er í á forsíðu og undirsíða -->
	
	<!-- Template fyrir seljanda í haus reiknings -->
	<xsl:template name="Seljandi">
		<div class="dalkfyrirsogn3">Seljandi:</div>
		<div>
			<div class="dalkfyrirsogn2">
				<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name"/>
			</div>
		</div>
		<div style="margin-top: 0.0em;">
			<div class="left60prc">
				<div>Kt.&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID"/>
				</div>
				<div>
					<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:StreetName"/>&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:BuildingNumber"/>
					<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:Postbox[.!='']">,&#160;Pósthólf:&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:Postbox"/>
					</xsl:if>
				</div>
				<xsl:choose>
					<xsl:when test="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:AdditionalStreetName">
						<div>
							<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:AdditionalStreetName"/>
						</div>
					</xsl:when>
				</xsl:choose>
				<div>
					<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:PostalZone"/>&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:CityName"/>,
					<xsl:choose>
						<xsl:when test="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:CountrySubentity">
							<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:CountrySubentity"/>,
			</xsl:when>
					</xsl:choose>
					<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cac:Country/cbc:IdentificationCode"/>
				</div>
			</div>
			<div>				
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:Department">
					<div><xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:Department"/></div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone[.!='']">
					<div>Sími:&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone"/></div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telefax[.!='']">
					<div>Fax:&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telefax"/></div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID[.!='']">
					<div>VSK nr.&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID"/></div>
				</xsl:if>
			</div>
		</div>
	</xsl:template>
	
	<!-- Template fyrir Reiknings upplýsingar -->
	<xsl:template name="Reikningur">
		<div>
			<div class="letur2">&#160;</div>
			<div class="letur2">&#160;</div>
			<div class="letur8">REIKNINGUR</div>
			<div class="letur7">Reikningsnr.&#160;<xsl:value-of select="cbc:ID"/>
			</div>
		</div>
	</xsl:template>
	
	<!-- Template fyrir kaupanda -->
	<xsl:template name="Greidandi">
		<div class="dalkfyrirsogn">Kaupandi:</div>
		
		<div style="margin-top: 1.2em; margin-left: 1.2em; line-height: 120%">
			<div class="ListItem">
				<b><xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name"/></b>
			</div>
			<div class="ListItem">
				<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:StreetName"/>&#160;<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:BuildingNumber"/>
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:Postbox[.!='']">,&#160;Pósthólf:&#160;<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:Postbox"/>
				</xsl:if>
			</div>
			
			<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:AdditionalStreetName[.!='']">
				<div class="ListItem">
					<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:AdditionalStreetName"/>
				</div>
			</xsl:if>
			
			<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:Department[.!='']">
				<div class="ListItem"><xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:Department"/>
				</div>			
			</xsl:if>
			
			<div class="ListItem">
				<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:PostalZone"/>&#160;<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:CityName"/>,
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:CountrySubentity[.!='']">
					<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:CountrySubentity"/>,
				</xsl:if>
				
				<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cac:Country/cbc:IdentificationCode"/>
			</div>
			<div class="ListItem">
            Kt.&#160;<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID"/>
			</div>
			
			<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID[.!='']">
				<div class="ListItem">VSK nr.&#160;<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID"/></div>
			</xsl:if>			
		</div>
	</xsl:template>
	
	<!-- Gjalddagi, eindagi og upphæð reiknings -->
	<xsl:template name="Summur">
		<div id="haussummurgjalddagi" class="righhaussummurgjalddagi">
			<!-- Gjalddagi -->	
			<div class="dalkfyrirsognbold">Gjalddagi</div>
			<div class="letur9" style="text-align: center;">
				<xsl:call-template name="icedate">
					<xsl:with-param name="text" select="cac:PaymentMeans/cbc:PaymentDueDate"/>
				</xsl:call-template>
			</div>
		</div>
		
		<!-- Eindagi -->
		<div id="haussummureindagi" class="righhaussummureindagi">
			<div class="dalkfyrirsognbold">Eindagi</div>
			<div class="letur9" style="text-align: center;">
				<xsl:call-template name="icedate">
					<xsl:with-param name="text" select="cac:PaymentTerms/cac:SettlementPeriod/cbc:StartDate"/>
				</xsl:call-template>
			</div>
		</div>
		
		<!-- Útgáfudagur reiknings -->
		<div id="haussummurreiknuppl" class="righhaussummurreiknuppl">
			<div class="letur1b">Útgáfudagur reiknings:</div>
			<div class="letur1">
				<xsl:call-template name="icedate">
					<xsl:with-param name="text" select="cbc:IssueDate"/>
				</xsl:call-template>
			</div>
			<!-- Reikningstímabil: -->
			<xsl:if test="cac:InvoicePeriod/cbc:StartDate[.!='']">
				<div class="letur1b">Reikningstímabil:</div>
				<div class="letur1">
					<xsl:call-template name="icedate">
						<xsl:with-param name="text" select="cac:InvoicePeriod/cbc:StartDate"/>
					</xsl:call-template>&#160;-&#160;<xsl:call-template name="icedate">
						<xsl:with-param name="text" select="cac:InvoicePeriod/cbc:EndDate"/>
					</xsl:call-template>
				</div>
			</xsl:if>
			<!-- Viðmiðunardagur skatts - yfirleitt ekki notað á Íslandi -->
			<xsl:if test="cbc:TaxPointDate[.!='']">
				<div class="letur1b">Viðmiðunardagur skatts:</div>
				<div class="letur1">
					<xsl:call-template name="icedate">
						<xsl:with-param name="text" select="cbc:TaxPointDate"/>
					</xsl:call-template>
				</div>
			</xsl:if>
		</div>
		<!-- Til greiðslu -->
		<div id="haussummurtilgreidslu" class="righhaussummurtilgreidslu">
			<div class="dalkfyrirsognbold">Til greiðslu</div>
			<div class="letur9" style="font-weight: bold;text-align: center;">
				<xsl:call-template name="icenumberdecdef">
					<xsl:with-param name="text" select="cac:LegalMonetaryTotal/cbc:PayableAmount"/>
				</xsl:call-template>
			</div>
		</div>
		<!-- Gjaldmiðill á reikningi -->
		<div class="letur1" style="text-align: right">Gjaldmiðill á reikningi:&#160;<xsl:value-of select="cbc:DocumentCurrencyCode"/>&#160;</div>
	</xsl:template>
	
	<!-- Greiðsluupplýsingar -->
	<xsl:template name="Greidsluupplysingar">
		<div class="leftgreidsluupplysingarhaus">Greiðsluupplýsingar</div>
		<div class="leftgreidsluupplysingarteg">
			<xsl:call-template name="Greidslumati"/>
		</div>
		<xsl:choose>
			<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='49']">
				<xsl:call-template name="GreidslumatiKrafa"/>
			</xsl:when>
			<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='42']">
				<xsl:call-template name="GreidslumatiMillifaersla"/>
			</xsl:when>
			<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='97']">
				<xsl:call-template name="GreidslumatiReikningsfaert"/>
			</xsl:when>
			<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='10']">
				<xsl:call-template name="GreidslumatiStadgreitt"/>
			</xsl:when>
			<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='20']">
				<xsl:call-template name="GreidslumatiAvisun"/>
			</xsl:when>			
			<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='31']">		
				<!--Hér þarf að greina á milli með IBAN hvort erlend eða innlend millifærsla-->
				<xsl:choose>
					<xsl:when test="cac:PaymentMeans/cbc:PaymentChannelCode[.='IBAN']">
						<xsl:call-template name="GreidslumatiErlendMillifaersla"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="GreidslumatiMillifaersla"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='50']">
				<xsl:call-template name="GreidslumatiGirosedill"/>
			</xsl:when>
			<xsl:otherwise/>
		</xsl:choose>
	</xsl:template>
	
	<!-- Lýsing hægra megin í haus -->
	<xsl:template name="Lysing">
		<div class="letur1b" style="display: inline">Lýsing: </div>
		<div class="letur1" style="display: inline">
			<xsl:value-of select="cbc:Note"/>
		</div>
		<div class="dalkfyrirsognbold">&#160;</div>
		<xsl:if test="cbc:AccountingCost[.!='']">
			<div class="letur1b" style="display: inline">Bókunarupplýsingar: </div>
			<div class="letur1" style="display: inline">
				<xsl:value-of select="cbc:AccountingCost"/>
			</div>
		</xsl:if>
		<xsl:if test="cac:ContractDocumentReference/cbc:ID[.!='']">
			<br/>
			<div class="letur1b" style="display: inline">Samningsnúmer: </div>
			<div class="letur1" style="display: inline">
				<xsl:value-of select="cac:ContractDocumentReference/cbc:ID"/>
			</div>
		</xsl:if>
		<xsl:if test="cac:ContractDocumentReference/cbc:DocumentType[.!='']">
			<br/>
			<div class="letur1b" style="display: inline">Samningstegund: </div>
			<div class="letur1" style="display: inline">
				<xsl:value-of select="cac:ContractDocumentReference/cbc:DocumentType"/>
			</div>
		</xsl:if>
		<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID[.!='']">
			<br/>
			<div class="letur1b" style="display: inline">Viðskiptareikningur: </div>
			<div class="letur1" style="display: inline">
				<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID"/>
			</div>
		</xsl:if>
		<xsl:if test="cac:OrderReference/cbc:ID[.!='']">
			<br/>
			<div class="letur1b" style="display: inline">Pöntun nr.: </div>
			<div class="letur1" style="display: inline">
				<xsl:value-of select="cac:OrderReference/cbc:ID"/>
			</div>
		</xsl:if>
		<xsl:if test="cac:Delivery/cbc:ActualDeliveryDate[.!='']">
			<br/>
			<div class="letur1b" style="display: inline">Afhending dags: </div>
			<div class="letur1" style="display: inline">
				<xsl:call-template name="icedate">
					<xsl:with-param name="text" select="cac:Delivery/cbc:ActualDeliveryDate"/>
				</xsl:call-template>
			</div>
		</xsl:if>
		<xsl:if test="cac:PaymentTerms/cbc:Note[.!='']">
			<br/>
			<div class="letur1b" style="display: inline">Skilmálar: </div>
			<div class="letur1" style="display: inline">
				<xsl:value-of select="cac:PaymentTerms/cbc:Note"/>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Línur reiknings -->
	<xsl:template name="Linur">
		<div>&#160;</div>
		<div id="linur" class="linur">
			<table width="100%" cellspacing="0" cellpadding="3" class="ntable" summary="Línur á reikningi">
				<tr height="25">
					<td width="3%" class="hdrcol22" align="right" nowrap="nowrap">&#160;</td>
					<td width="6%" class="hdrcol22" align="left" nowrap="nowrap">Vörunr.</td>
					<td width="33%" class="hdrcol22" align="left">Lýsing</td>
					<td width="6%" class="hdrcol22" align="right" style="padding-right: 0.4em;" nowrap="nowrap">Magn</td>
					<td width="6%" class="hdrcol22" align="center" nowrap="nowrap">Ein.</td>
					<td width="6%" class="hdrcol22" align="right" nowrap="nowrap">Ein.verð<sup>1</sup></td>
					<td width="6%" class="hdrcol22" align="center" nowrap="nowrap">VSK.</td>
					<td width="8%" class="hdrcol22" align="right" nowrap="nowrap">Afsl./Gjöld<sup>2</sup></td>
					<td width="12%" class="hdrcol22" align="right" nowrap="nowrap">Upphæð</td>
					<td width="14%" class="hdrcol22" align="right" nowrap="nowrap">Upphæð m. VSK.<sup>3</sup>&#160;</td>
				</tr>
				<xsl:for-each select="cac:InvoiceLine">
					<tr class="col2{position() mod 2}" style="border-left: 0.1em solid #A6C3D1; border-right: 0.1em solid #A6C3D1;">
						<td width="3%" valign="top" align="right" class="hdrcol23" nowrap="nowrap">
							<!-- linu nr -->
							<xsl:value-of select="cbc:ID"/>.&#160;
						</td>
						<td width="6%" valign="top" align="left" class="hdrcol23" nowrap="nowrap">
							<!-- vörunúmer -->
							<xsl:value-of select="cac:Item/cac:SellersItemIdentification/cbc:ID"/>
						</td>
						<td width="33%" valign="top" align="left" class="hdrcol23">
							<!-- Nafn vöru -->
							<xsl:value-of select="cac:Item/cbc:Name"/>
						</td>
						<td width="6%" valign="top" align="right" class="hdrcol23" nowrap="nowrap">
							<!-- Magn -->
							<xsl:call-template name="icenumberdecdef">
								<xsl:with-param name="text" select="cbc:InvoicedQuantity"/>
							</xsl:call-template>&#160;
						</td>
						<td width="6%" valign="top" align="center" class="hdrcol23" nowrap="nowrap">
							<!-- Einingar -->
							<xsl:call-template name="Einingar">
								<xsl:with-param name="text" select="cbc:InvoicedQuantity/@unitCode"/>
							</xsl:call-template>
						</td>
						<td width="6%" valign="top" align="right" class="hdrcol23" nowrap="nowrap">
							<!-- Ein.verð*-->
							<xsl:call-template name="icenumberdecdef">
								<xsl:with-param name="text" select="cac:Price/cbc:PriceAmount"/>
							</xsl:call-template>
						</td>
						<td width="6%" valign="top" align="center" class="hdrcol23" nowrap="nowrap">
							<!-- VSK-->
							<xsl:value-of select="cac:Item/cac:ClassifiedTaxCategory/cbc:ID"/>
						</td>
						<td width="8%" valign="top" align="right" class="hdrcol23" nowrap="nowrap">
							<!-- Afsl. kr.-->
							<xsl:call-template name="samtalaafslatta"/>
						</td>
						<td width="12%" valign="top" align="right" class="hdrcol23" nowrap="nowrap">
							<!-- Upphæð -->
							<xsl:call-template name="icenumberdecdef">
								<xsl:with-param name="text" select="cbc:LineExtensionAmount"/>
							</xsl:call-template>
						</td>
						<td width="14%" valign="top" align="right" class="hdrcol23" nowrap="nowrap">
							<!-- Upphæð m.vsk -->
							<xsl:choose>
								<xsl:when test="cac:TaxTotal/cbc:TaxAmount[.!='']">
									<xsl:call-template name="upphaedmvsktaxamount"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:call-template name="upphaedmvskreiknad"/>
								</xsl:otherwise>
							</xsl:choose>
							&#160;
						</td>
					</tr>
				</xsl:for-each>
			</table>
			</div>
	</xsl:template>
	
	<!-- Samtölur reiknings -->
<xsl:template name="Samtolur">
	<div class="upphaedsamantekt">
		<table width="100%" cellspacing="0" cellpadding="0" summary="Samtolur2" border="0">
			<tr>
				<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>					
				<td width="120px" class="hdrcol23" align="right" nowrap="nowrap">Samtals:</td>
				<td width="87px" class="hdrcol23" align="right" nowrap="nowrap">
					<xsl:value-of select='format-number(sum(cac:InvoiceLine/cbc:LineExtensionAmount), "###.###,##", "IcelandicNumber")'/>
				</td>
				<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
			</tr>
			<xsl:if test="cac:LegalMonetaryTotal/cbc:ChargeTotalAmount[.!='']">
				<tr>
					<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>					
					<td width="120px" class="hdrcol23" align="right" nowrap="nowrap">
						Samtala gjalda í haus:
					</td>
					<td width="87px" class="hdrcol23" align="right" nowrap="nowrap">
						<xsl:value-of select='format-number(round(cac:LegalMonetaryTotal/cbc:ChargeTotalAmount), "###.###,00", "IcelandicNumber")'/>
					</td>
					<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
				</tr>
			</xsl:if>
			<xsl:if test="cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount[.!='']">
				<tr>
					<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>					
					<td width="120px" class="hdrcol23" align="right" nowrap="nowrap">
						Samtala afslátta í haus:
					</td>
					<td width="87px" class="hdrcol23" align="right" nowrap="nowrap">
						<xsl:value-of select='format-number(round(cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount), "###.###,00", "IcelandicNumber")'/>
					</td>
					<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
				</tr>
			</xsl:if>
				<tr>
					<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>					
					<td width="120px" class="hdrcol23" align="right" nowrap="nowrap">Samtals VSK:</td>
					<td width="87px" class="hdrcol23" align="right" nowrap="nowrap">
						<xsl:value-of select='format-number(sum(cac:TaxTotal/cac:TaxSubtotal/cbc:TaxAmount), "###.###,##", "IcelandicNumber")'/>
					</td>
					<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
				</tr>
				<tr>
					<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
					<td width="120px" class="hdrcol234" align="right" nowrap="nowrap">Afrúningur aura:</td>
					<td width="87px" class="hdrcol234" align="right" nowrap="nowrap">
						<xsl:call-template name="icenumberdecdef">
							<xsl:with-param name="text" select="cac:LegalMonetaryTotal/cbc:PayableRoundingAmount"/>
						</xsl:call-template>
					</td>
					<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
				</tr>
				
				<tr>
					<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>					
					<td width="120px" class="hdrcol23" align="right" nowrap="nowrap">
						<b>Samtala Reiknings:</b>
					</td>
					<td width="87px" class="hdrcol23" align="right" nowrap="nowrap">
						<b>
							<xsl:value-of select='format-number(round(cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount), "###.###,00", "IcelandicNumber")'/>
						</b>
					</td>
					<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
				</tr>
			<xsl:if test="cac:LegalMonetaryTotal/cbc:PrepaidAmount[.!='']">				
				<tr>
					<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>	
					<td width="120px" class="hdrcol234" align="right" nowrap="nowrap">Fyrirfram greitt:</td>
					<td width="87px" class="hdrcol234" align="right" nowrap="nowrap">
						<xsl:value-of select='format-number(round(cac:LegalMonetaryTotal/cbc:PrepaidAmount), "###.###,00", "IcelandicNumber")'/>
					</td>
					<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
				</tr>
				
				<tr>
					<td width="57px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>	
					<td width="120px" class="hdrcol23" align="right" nowrap="nowrap">
						<b>Samtals til greiðslu:</b>
					</td>
					<td width="87px" class="hdrcol23" align="right" nowrap="nowrap">
						<b>
							<xsl:value-of select='format-number(round(cac:LegalMonetaryTotal/cbc:PayableAmount), "###.###,00", "IcelandicNumber")'/>
						</b>
					</td>
					<td width="118px" class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
				</tr>
				</xsl:if>
			</table>
		</div>
	</xsl:template>

	<!-- Vsk. upplýsingar ásamt skýringum vegna eininga -->
<xsl:template name="SkattarOgAfslnytt">
	<div class="vsksamantekt">
			<table width="100%" cellspacing="0" cellpadding="3" summary="skýringar">
				<tr>
					<td width="18%" class="hdrcolSmall" align="left">&#160;Fjöldi lína: <xsl:value-of select='format-number(count(cac:InvoiceLine/cbc:ID), "###.###", "IcelandicNumber")'/></td>
					<td width="80%" class="hdrcolSmall" align="left" nowrap="nowrap"><sup>1</sup> Sjá "Ítarupplýsingar um afslátt eða gjald á einingarverð"</td>
				</tr>
				<tr>
					<td width="18%" class="hdrcolSmall" align="left">&#160;</td>
					<td width="80%" class="hdrcolSmall" align="left" nowrap="nowrap"><sup>2</sup> Sjá "Ítarupplýsingar um afslætti og gjöld á línum"</td>
				</tr>
				<tr>
					<td width="18%" class="hdrcolSmall" align="left">&#160;</td>
					<td width="80%" class="hdrcolSmall" align="left" nowrap="nowrap"><sup>3</sup> Til upplýsinga eingöngu</td>
				</tr>
			</table>						
			<br/>
			<br/>
			<table width="100%" cellspacing="0" cellpadding="2" class="ntable" summary="Skattar samtölur">
				<thead>
					<tr height="25">
						<td align="right" class="hdrcol22" colspan="3">Samantekt á VSK.</td>
						<td align="right" class="hdrcol22">Skattskyld upphæð</td>
						<td align="right" class="hdrcol22">Skattur&#160;</td>
					</tr>
				</thead>
				<tbody>
					<xsl:for-each select="cac:TaxTotal">
						<xsl:for-each select="cac:TaxSubtotal">
							<tr height="20">
								<td width="4%" align="right"/>
								<td width="6%" align="left" class="hdrcol23">
									<xsl:value-of select="cac:TaxCategory/cbc:ID"/>
								</td>
								<td width="20%" align="right" class="hdrcol23">(<xsl:call-template name="icenumberdecdef2">
									<xsl:with-param name="text" select="cac:TaxCategory/cbc:Percent"/>
									</xsl:call-template>%)
                     			</td>
								<td width="33%" align="right" class="hdrcol23">
									<xsl:call-template name="icenumberdecdef">
										<xsl:with-param name="text" select="cbc:TaxableAmount"/>
									</xsl:call-template>
								</td>
								<td width="37%" align="right" class="hdrcol23">
									<xsl:call-template name="icenumberdecdef">
										<xsl:with-param name="text" select="cbc:TaxAmount"/>
									</xsl:call-template>&#160;
                      			</td>
							</tr>
						</xsl:for-each>
					</xsl:for-each>
					<tr height="25">
						<td align="right" class="hdrcol23" style="border-top: 0.1em solid #A6C3D1;" colspan="5">
							<b>Samtals virðisaukaskattur:&#160;&#160;<xsl:value-of select='format-number(sum(cac:TaxTotal/cac:TaxSubtotal/cbc:TaxAmount), "###.###,##", "IcelandicNumber")'/>&#160;</b>
						</td>
					</tr>
				<xsl:if test="(cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReasonCode) or (cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReason) [.!='']">
					<tr>
						<td align="right" style="border-top: 0.1em solid #A6C3D1;" colspan="5">
							<div class="skattur">Ástæða skatt undanþágu:
								<xsl:if test="cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReasonCode[.!='']">
									<xsl:value-of select="cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReasonCode"/>
								</xsl:if>
								<xsl:if test="(cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReasonCode) and (cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReason) [.!='']">
									-
								</xsl:if>
								<xsl:if test="cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReason[.!='']">
									<xsl:value-of select="cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReason"/>
								</xsl:if>
							</div>
						</td>
					</tr>
				</xsl:if>
			</tbody>
		</table>
	</div>
</xsl:template>
	

	

	<!-- Afhendingarstaður -->
<xsl:template name="AnnadAfhendingarstadur">
	<xsl:if test="(cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:StreetName) or
			(cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:BuildingNumber) or
			(cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:AdditionalStreetName) or
			(cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:Department) or 
			(cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:PostalZone) or
			(cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:CountrySubentity) or
			(cac:Delivery/cac:DeliveryLocation/cac:Address/cac:Country/cbc:IdentificationCode) or
			(cac:Delivery/cac:DeliveryLocation/cbc:ID) [.!='']">
		<div class="floatkassi">
		<div class="itarupplBig">Afhendingarstaður:</div>
			<div class="afhending">
				<div>
					<xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:StreetName"/>&#160;<xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:BuildingNumber"/>
				</div>
				<xsl:if test="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:AdditionalStreetName[.!='']">
					<div><xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:AdditionalStreetName"/></div>
				</xsl:if>				
				<xsl:if test="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:Department[.!='']">
					<div><xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:Department"/></div>
				</xsl:if>
				<div>
					<xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:PostalZone"/>&#160;<xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:CityName"/>,
						<xsl:if test="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:CountrySubentity[.!='']">
							<xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:CountrySubentity"/>,
						</xsl:if>
					<xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cac:Address/cac:Country/cbc:IdentificationCode"/>
				</div>
				<xsl:if test="cac:Delivery/cac:DeliveryLocation/cbc:ID[.!='']">
					<div>
						<b>Auðkenni afhendingarstaðar:</b>
					</div>
					<div><xsl:value-of select="cac:Delivery/cac:DeliveryLocation/cbc:ID"/></div>
				</xsl:if>
			</div>
		</div>
	</xsl:if>
</xsl:template>
	
<!-- Tengiliður Seljanda -->
<xsl:template name="TengilidurSeljanda">	
	<xsl:if test="(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:FirstName) or 
			(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:MiddleName) or
			(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:FamilyName) or
			(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:JobTitle) or
			(cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail) or
			(cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone) or
			(cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telefax) or
			(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName) or
			(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName) or
			(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity) or
			(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:Country/cbc:IdentificationCode) or
			(cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:ID) or
			(cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID) [.!='']">
		<div class="tengilidirseljanda">	
		<div class="itarupplBig">Ítarupplýsingar seljanda:</div>			
			<div class="minifloat">	
				<xsl:if test="(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:FirstName) or
						(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:MiddleName) or
						(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:FamilyName) or
						(cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:JobTitle) [.!='']">
					<div><b>Tengiliður seljanda:</b></div>
					<div>
						<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:FirstName"/>&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:MiddleName"/>&#160;<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:FamilyName"/>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Person/cbc:JobTitle"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail[.!='']">
					<div>
						E-mail: <xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone[.!='']">
					<div>
						Sími: <xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telefax[.!='']">
					<div>
						Fax: <xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telefax"/>
					</div>
				</xsl:if>
				<xsl:if test="(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName) or
						(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName) or
						(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity) or
						(cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:Country/cbc:IdentificationCode) [.!='']">
					<div>
						<b>Lögskráð nafn og heimili:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName"/>
					</div>
					<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName">
						<div>
							<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName"/>,
							<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity[.!='']">
								<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity"/>,
							</xsl:if>
							<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cac:Country/cbc:IdentificationCode"/>
						</div>
					</xsl:if>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:ID[.!='']">
					<div>
						<b>Auðkenni staðsetningar:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:ID"/>
						<xsl:for-each select="cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:ID">
							(<xsl:value-of select="@schemeID"/>)
						</xsl:for-each>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID[.!='']">
					<div>
						<b>Viðskiptamannanúmer:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingSupplierParty/cac:Party/cbc:EndpointID[.!='']">
					<div>
						<b>Rafrænt póstfang:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cbc:EndpointID"/>
						<xsl:for-each select="cac:AccountingSupplierParty/cac:Party/cbc:EndpointID">
							(<xsl:value-of select="@schemeID"/>)
						</xsl:for-each>
					</div>
				</xsl:if>
			</div>
		</div>
	</xsl:if>
</xsl:template>
	
<!-- Tengiliður kaupanda -->
<xsl:template name="TengilidurKaupanda">
	<xsl:if test="(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FirstName) or
			(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:MiddleName) or
			(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FamilyName) or
			(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:JobTitle) or
			(cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail) or
			(cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telephone) or
			(cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telefax) or
			(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName) or
			(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName) or
			(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity) or
			(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:Country/cbc:IdentificationCode) or
			(cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:ID) or
			(cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID) [.!='']">		
		<div class="tengilidirkaupanda">
		<div class="itarupplBig">Ítarupplýsingar kaupanda:</div>			
			<div class="minifloat">
				<xsl:if test="(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FirstName) or
						(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:MiddleName) or
						(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FamilyName) or
						(cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:JobTitle) [.!='']">
					<div>
						<b>Tengiliður kaupanda:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FirstName"/>&#160;<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:MiddleName"/>&#160;<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:FamilyName"/>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:Person/cbc:JobTitle"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail[.!='']">
					<div>
						E-mail: <xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telephone[.!='']">
					<div>
						Sími: <xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telephone"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telefax[.!='']">
					<div>
						Fax: <xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telefax"/>
					</div>
				</xsl:if>
				<xsl:if test="(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName) or
						(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName) or
						(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity) or
						(cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:Country/cbc:IdentificationCode) [.!='']">
					<div>
						<b>Lögskráð nafn og heimili:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName"/>
					</div>
					<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName">
						<div>
							<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CityName"/>, 
							<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity[.!='']">
								<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cbc:CountrySubentity"/>, 
							</xsl:if>
							<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cac:RegistrationAddress/cac:Country/cbc:IdentificationCode"/>
						</div>
					</xsl:if>
				</xsl:if>
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:ID[.!='']">
					<div>
						<b>Auðkenni staðsetningar:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:ID"/>
						<xsl:for-each select="cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:ID">
							(<xsl:value-of select="@schemeID"/>)
						</xsl:for-each>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID[.!='']">
					<div>
						<b>Viðskiptamannanúmer:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID"/>
					</div>
				</xsl:if>
				<xsl:if test="cac:AccountingCustomerParty/cac:Party/cbc:EndpointID[.!='']">
					<div>
						<b>Rafrænt póstfang:</b>
					</div>
					<div>
						<xsl:value-of select="cac:AccountingCustomerParty/cac:Party/cbc:EndpointID"/>
						<xsl:for-each select="cac:AccountingCustomerParty/cac:Party/cbc:EndpointID">
							(<xsl:value-of select="@schemeID"/>)
						</xsl:for-each>
					</div>
				</xsl:if>
			</div>
		</div>
	</xsl:if>
</xsl:template>
	
<!-- Ítarupplýsingar á línum -->
<xsl:template name="AnnadItarupplLinu">
	<div class="itarupplBig">Ítarupplýsingar á línum:</div>
	<table class="ntable" cellpadding="1" cellspacing="0" width="100%" summary="Greiðsluupplýsingar" border="0">
		<tr height="25">
			<td width="1%" class="hdrcol22" align="center" nowrap="nowrap">&#160;</td>
			<td width="3%" class="hdrcol22" align="left" nowrap="nowrap">&#160;</td>
			<td width="14%" class="hdrcol22" align="left" nowrap="nowrap">Vörunr. birgja</td>
			<td width="18%" class="hdrcol22" align="left" nowrap="nowrap">Vörunr. staðlað</td>
			<td width="16%" class="hdrcol22" align="left">Vöruflokkun</td>
			<td width="16%" class="hdrcol22" align="left" style="padding-right: 0.4em;" nowrap="nowrap">Bókunarupplýsingar</td>
			<td width="16%" class="hdrcol22" align="left" nowrap="nowrap">Pöntun</td>
			<td width="15%" class="hdrcol22" align="left" nowrap="nowrap">Lína pöntunar</td>
			<td width="1%" class="hdrcol22" align="left" nowrap="nowrap">&#160;</td>
		</tr>
		<xsl:for-each select="cac:InvoiceLine">
			<tr>
				<td class="hdrcol23" align="left" nowrap="nowrap">&#160;</td>
				<td valign="top" align="left" class="hdrcol23" nowrap="nowrap">
					<!-- vörunúmer -->
					<xsl:value-of select="cbc:ID"/>
				</td>
				<td valign="top" align="left" class="hdrcol23" nowrap="nowrap">
					<!-- vörunúmer -->
					<xsl:value-of select="cac:Item/cac:SellersItemIdentification/cbc:ID"/>
				</td>
				<td valign="top" align="left" class="hdrcol23">
					<!-- Lýsing -->
					<xsl:value-of select="cac:Item/cac:StandardItemIdentification/cbc:ID"/>
					<xsl:for-each select="cac:Item/cac:StandardItemIdentification/cbc:ID">
						(<xsl:value-of select="@schemeID"/>)
					</xsl:for-each>
				</td>
				<td valign="top" align="left" class="hdrcol23" nowrap="nowrap">
					<!-- vörunúmer -->
					<xsl:value-of select="cac:Item/cac:CommodityClassification/cbc:ItemClassificationCode"/>
					<xsl:for-each select="cac:Item/cac:CommodityClassification/cbc:ItemClassificationCode">
						(<xsl:value-of select="@listID"/>)
					</xsl:for-each>
				</td>
				<td valign="top" align="left" class="hdrcol23">
					<!-- Lýsing -->
					<xsl:value-of select="cbc:AccountingCost"/>
				</td>
				<td valign="top" align="left" class="hdrcol23" nowrap="nowrap">
					<!-- vörunúmer -->
					<xsl:value-of select="cac:OrderLineReference/cac:OrderReference/cbc:ID"/>
				</td>
				<td valign="top" align="left" class="hdrcol23">
					<!-- Lýsing -->
					<xsl:value-of select="cac:OrderLineReference/cbc:LineID"/>
				</td>
				<td valign="top" align="left" class="hdrcol23"/>
			</tr>
			<xsl:if test="cac:Item/cbc:Description[.!='']">
				<tr>
					<td/>
					<td colspan="7" class="hdrcol23">
						<xsl:value-of select="cac:Item/cbc:Description"/>
					</td>
					<td/>
				</tr>
			</xsl:if>
			<xsl:if test="cac:Item/cac:AdditionalItemProperty[.!='']">
				<tr valign="top">
					<td/>
					<td colspan="8">
						<table summary="Lýsing vöru" border="0" width="100%" cellpadding="0" cellspacing="0">
							<xsl:for-each select="cac:Item/cac:AdditionalItemProperty">
								<tr>
									<td height="5px" valign="bottom" align="left" class="hdrcolimage">
										<div class="ell"></div>
									</td>
									<td width="98%" valign="bottom" align="left" class="hdrcol23">
										<xsl:value-of select="cbc:Name"/> - <xsl:value-of select="cbc:Value"/>
									</td>
								</tr>
							</xsl:for-each>
						</table>
					</td>
				</tr>
			</xsl:if>
			<xsl:if test="cbc:Note[.!='']">
				<tr>
					<td/>
					<td colspan="7" class="hdrcol23">
						<b>Athugasemd: </b>
						<xsl:value-of select="cbc:Note"/>
					</td>
					<td/>
				</tr>
			</xsl:if>
		</xsl:for-each>
	</table>
</xsl:template>
	
	
<!-- Ítarupplýsingar um afslætti og gjöld á línum -->
<xsl:template name="AnnadItarupplLinuAfls">
	<xsl:if test="cac:InvoiceLine/cac:Price/cac:AllowanceCharge/cbc:ChargeIndicator">
		<div class="itarupplBig">Ítarupplýsingar um afslátt eða gjald á einingarverð:</div>
		<table class="ntable" cellpadding="1" cellspacing="0" width="100%" summary="Ítarupplýsingar um afslætti og gjöld á einingarverð" border="0">
			<tr height="25">
				<td width="8%" class="hdrcol22" align="left" nowrap="nowrap">&#160;&#160;Línunr.</td>
				<td width="36%" class="hdrcol22" align="left" nowrap="nowrap">Skýring</td>
				<td width="10%" class="hdrcol22" align="left" nowrap="nowrap">Tegund</td>
				<xsl:if test="$ergrunnupph > 0">
					<td width="10%" class="hdrcol22" align="right">Listaverð</td>
				</xsl:if>
				<xsl:if test="$erpro > 0">
					<td width="10%" class="hdrcol22" align="right">Prósenta </td>
				</xsl:if>
				<td width="13%" class="hdrcol22" align="right" nowrap="nowrap">Upphæð</td>
				<td width="13%" class="hdrcol22" align="right" nowrap="nowrap">Einingarverð*&#160;&#160;</td>
				<xsl:if test="$erpro = 0">
					<td width="10%" class="hdrcol22" align="Right">&#160;</td>
				</xsl:if>
				<xsl:if test="$ergrunnupph = 0">
					<td width="10%" class="hdrcol22" align="right">&#160;</td>
				</xsl:if>
			</tr>
			<xsl:for-each select="cac:InvoiceLine/cac:Price/cac:AllowanceCharge">
				<xsl:if test=".!=''">
					<tr height="25">
						<td class="hdrcol23" align="left" nowrap="nowrap">&#160;&#160;<xsl:value-of select="../../cbc:ID"/>.</td>
						<td class="hdrcol23" align="left">
							<xsl:value-of select="cbc:AllowanceChargeReason"/>
						</td>
						<td class="hdrcol23" align="left" nowrap="nowrap">
							<xsl:call-template name="afslatturgjold">
								<xsl:with-param name="afsl" select="cbc:ChargeIndicator"/>
							</xsl:call-template>
						</td>
						<xsl:if test="$ergrunnupph > 0">
							<td class="hdrcol23" align="right" nowrap="nowrap">
								<xsl:if test="cbc:BaseAmount">
									<xsl:value-of select='format-number(cbc:BaseAmount,"###.###,00", "IcelandicNumber")'/>
								</xsl:if>
							</td>
						</xsl:if>
						<xsl:if test="$erpro > 0">
							<td class="hdrcol23" align="right" nowrap="nowrap">
								<xsl:if test="cbc:MultiplierFactorNumeric">
									<xsl:value-of select='format-number(cbc:MultiplierFactorNumeric *100,"###.###,00", "IcelandicNumber")'/>
								</xsl:if>	
							</td>
						</xsl:if>
						<td class="hdrcol23" align="right" nowrap="nowrap">
							<xsl:value-of select='format-number(cbc:Amount,"###.###,00", "IcelandicNumber")'/></td>
						<td class="hdrcol23" align="right" nowrap="nowrap">
							<xsl:value-of select='format-number(../cbc:PriceAmount,"###.###,00", "IcelandicNumber")'/>&#160;&#160;
						</td>
						<xsl:if test="$erpro = 0">
							<td class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
						</xsl:if>
						<xsl:if test="$ergrunnupph = 0">
							<td class="hdrcol23" align="right" nowrap="nowrap">&#160;</td>
						</xsl:if>
					</tr>
				</xsl:if>
			</xsl:for-each>
		</table>
		<table width="100%" cellspacing="0" cellpadding="3" summary="skýringar">
			<tr>
				<td width="97%" class="hdrcolSmall" align="left" nowrap="nowrap">&#160;&#160;*Ath. Einingarverð er birt með tilliti til afslátta/gjalda og án VSK.</td>
			</tr>
		</table>
	</xsl:if>
</xsl:template>

	<!-- Ítarupplýsingar um afslætti og gjöld á haus -->
<xsl:template name="AnnadItarupplLinuAflshaus">
	<xsl:if test="cac:AllowanceCharge/cbc:ChargeIndicator">
		<div class="itarupplBig">Ítarupplýsingar um afslætti og gjöld á haus:</div>
		<table class="ntable" cellpadding="1" cellspacing="0" width="100%" summary="Ítarupplýsingar um afslætti og gjöld á haus" border="0">
			<tr height="25">
				<td width="10%" class="hdrcol22" align="left" nowrap="nowrap">&#160;&#160;Tegund</td>
				<td width="10%" class="hdrcol22" align="right" nowrap="nowrap">Upphæð</td>
				<td width="3%" class="hdrcol22" align="left" nowrap="nowrap">&#160;&#160;</td>
				<td width="77%" class="hdrcol22" align="left" nowrap="nowrap">Skýring</td>
			</tr>
			<xsl:for-each select="cac:AllowanceCharge">
				<xsl:if test=".!=''">
					<tr height="25">
						<td class="hdrcol23" align="left" nowrap="nowrap">
							&#160;&#160;<xsl:call-template name="afslatturgjoldlinum"/>
						</td>
						<td class="hdrcol23" align="right" nowrap="nowrap">
							<xsl:value-of select='format-number(cbc:Amount,"###.###,00", "IcelandicNumber")'/>
						</td>
						<td class="hdrcol23" align="right" nowrap="nowrap">&#160;&#160;</td>	
						<td class="hdrcol23" align="left" nowrap="nowrap">
							<xsl:value-of select="cbc:AllowanceChargeReason"/>
						</td>
					</tr>
				</xsl:if>
			</xsl:for-each>
		</table>
	</xsl:if>
</xsl:template>
	
	<!-- Ítarupplýsingar um afslætti og gjöld á línum -->
<xsl:template name="AnnadItarupplLinuAfls2">
	<xsl:if test="cac:InvoiceLine/cac:AllowanceCharge/cbc:ChargeIndicator">
		<div class="itarupplBig">Ítarupplýsingar um afslætti og gjöld á línum:</div>
		<table class="ntable" cellpadding="1" cellspacing="0" width="100%" summary="Ítarupplýsingar um afslætti og gjöld á línum" border="0">
			<tr height="25">
				<td width="8%" class="hdrcol22" align="left" nowrap="nowrap">&#160;&#160;Línunr.</td>
				<td width="10%" class="hdrcol22" align="left" nowrap="nowrap">Tegund</td>
				<td width="13%" class="hdrcol22" align="right" nowrap="nowrap">Upphæð</td>
				<td width="3%" class="hdrcol22" align="left" nowrap="nowrap">&#160;&#160;</td>
				<td width="66%" class="hdrcol22" align="left" nowrap="nowrap">Skýring</td>
			</tr>
			<xsl:for-each select="cac:InvoiceLine/cac:AllowanceCharge">
				<xsl:if test=".!=''">
					<tr height="25">
						<td class="hdrcol23" align="left" nowrap="nowrap">
							&#160;&#160;<xsl:value-of select="../cbc:ID"/>.
						</td>
						<td class="hdrcol23" align="left" nowrap="nowrap">
							<xsl:call-template name="afslatturgjoldlinum"/>
						</td>
						<td class="hdrcol23" align="right" nowrap="nowrap">
							<xsl:value-of select='format-number(cbc:Amount,"###.###,00", "IcelandicNumber")'/></td>
						<td class="hdrcol23" align="left" nowrap="nowrap">&#160;&#160;</td>
						<td class="hdrcol23" align="left" nowrap="nowrap">
							<xsl:value-of select="cbc:AllowanceChargeReason"/>
						</td>		
					</tr>
				</xsl:if>
			</xsl:for-each>
		</table>
	</xsl:if>
</xsl:template>	

	<!-- Upplýsingar fyrir breytuna erpro -->
<xsl:template name="ProsentaHeader">
	<xsl:param name="count" select="0"/>
	<xsl:for-each select="n1:Invoice/cac:InvoiceLine/cac:Price/cac:AllowanceCharge">
		<xsl:if test="cbc:MultiplierFactorNumeric[.!='']">
			<xsl:call-template name="ProsentaHeader">
				<xsl:with-param name="count" select="$count + 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:for-each>
		<xsl:if test="$count > 0">1</xsl:if>
		<xsl:if test="$count = 0">0</xsl:if>
</xsl:template>
	
	<!-- Upplýsingar fyrir breytuna ergrunnupph -->
<xsl:template name="GrunnupphaedHeader">
	<xsl:param name="count2" select="0"/>
	<xsl:for-each select="n1:Invoice/cac:InvoiceLine/cac:Price/cac:AllowanceCharge">
		<xsl:if test="cbc:BaseAmount[.!='']">
			<xsl:call-template name="GrunnupphaedHeader">
				<xsl:with-param name="count2" select="$count2 + 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:for-each>
	<xsl:if test="$count2 > 0">1</xsl:if>
	<xsl:if test="$count2 = 0">0</xsl:if>
</xsl:template>
	
	<!-- Viðhengi -->	
<xsl:template name="AnnadVidhengi2">
	<xsl:if test="cac:AdditionalDocumentReference/cbc:ID[.!='']">
		<div class="itarupplBig">Viðhengi:</div>
		<table class="ntable" cellpadding="1" cellspacing="0" width="100%" summary="Viðhengi" border="0">
			<tr height="25">
				<td width="10%" class="hdrcol22" align="left" nowrap="nowrap">&#160;&#160;Nr.</td>
				<td width="18%" class="hdrcol22" align="left" nowrap="nowrap">Tegund</td>
				<td width="72%" class="hdrcol22" align="left" nowrap="nowrap">Tilvísun</td>
			</tr>
			<xsl:for-each select="cac:AdditionalDocumentReference">
				<tr height="25">
					<td class="hdrcol23" align="left" nowrap="nowrap">&#160;&#160;<xsl:value-of select="cbc:ID"/>.</td>
					<td class="hdrcol23" align="left" nowrap="nowrap">
						<xsl:value-of select="cbc:DocumentType"/>
					</td>
					<td class="hdrcol23" align="left" nowrap="nowrap">
						<xsl:variable name="urlString" select="cac:Attachment/cac:ExternalReference/cbc:URI"/>
						<a href="{$urlString}">
						<xsl:value-of select="cac:Attachment/cac:ExternalReference/cbc:URI"/>
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:if>
</xsl:template>
	
	<!-- Skýring greiðslumáta -->	
<xsl:template name="Greidslumati">
	<xsl:choose>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='42']">Millifærsla</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='10']">Staðgreitt</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='20']">Greitt með ávísun</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='49']">Krafa vegna reiknings</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='97']">Reikningsfært</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='50']">Gíróseðill</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='48']">Greitt með korti</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='93']">Greitt með korti</xsl:when>
		<xsl:when test="cac:PaymentMeans/cbc:PaymentMeansCode[.='31']">
	<!--Hér þarf að horfa á IBAN PaymentChannelCode ef IS:BANK þá innlend Millifærsla ef IBAN þá erlend millifærsla-->
		<xsl:choose>
			<xsl:when test="cac:PaymentMeans/cbc:PaymentChannelCode[.='IBAN']">Erlend Millifærsla</xsl:when>
			<xsl:otherwise>Innlend Millifærsla</xsl:otherwise>
		</xsl:choose>
		</xsl:when>
		<xsl:otherwise>Ekki til staðar</xsl:otherwise>
	</xsl:choose>
</xsl:template>
	

<!-- Greiðslumáti Krafa -->
<xsl:template name="GreidslumatiKrafa">
	<div class="leftgreidsluupplysingaruppl">
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder">
			<tr>
				<td width="24%" align="left">
					<div class="letur1b">Kennitala:</div>
				</td>
				<td width="18%" align="left">
					<div class="letur1b">Kröfunr.</div>
				</td>
				<td width="10%" align="left">
					<div class="letur1b">Fl.</div>
				</td>
				<td width="14%" align="left">
					<div class="letur1b">Banki:</div>
				</td>
				<td width="18%" align="left">
					<div class="letur1b">Höfuðbók:</div>
				</td>
				<td width="16%" align="left">
					<div class="letur1b">Gjalddagi:</div>
				</td>
			</tr>
			<tr>
				<td width="24%" align="left">
					<div class="letur1">
						<xsl:choose>
							<xsl:when test="cac:PayeeParty/cac:PartyLegalEntity/cbc:CompanyID[.!='']">
								<xsl:call-template name="kennitala">
									<xsl:with-param name="text" select="cac:PayeeParty/cac:PartyLegalEntity/cbc:CompanyID"/>
								</xsl:call-template>
							</xsl:when>
							<xsl:otherwise>
								<xsl:call-template name="kennitala">
									<xsl:with-param name="text" select="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID"/>
								</xsl:call-template>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</td>
				<td width="18%" align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:ID"/>
					</div>
				</td>
				<td width="10%" align="left">
					<div class="letur1">03</div>
				</td>
				<td width="14%" align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cac:FinancialInstitutionBranch/cbc:ID"/>
					</div>
				</td>
				<td width="18%" align="left">
					<div class="letur1">
						<xsl:call-template name="hofudbok">
							<xsl:with-param name="text" select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:AccountTypeCode"/>
						</xsl:call-template>
					</div>
				</td>
				<td width="16%" align="left">
					<div class="letur1">
						<xsl:call-template name="icedateshort">
							<xsl:with-param name="text" select="cac:PaymentMeans/cbc:PaymentDueDate"/>
						</xsl:call-template>
					</div>
				</td>
			</tr>
		</table>
		<p style="line-height:0.5em"/>
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder">
			<tr>
				<td>
					<div class="letur1b">
						<xsl:call-template name="MottakandiGreidsluFyrirsogn"/>
					</div>
					<div class="letur1">
						<xsl:call-template name="MottakandiGreidslu"/>
					</div>
				</td>
				<xsl:if test="cac:PaymentMeans/cbc:PaymentID[.!='']">
					<td>
						<div class="letur1">&#160;</div>
						<div class="letur1b">Tilvísun greiðslu:</div>
						<div class="letur1">
							<xsl:value-of select="cac:PaymentMeans/cbc:PaymentID"/>
						</div>
						<div class="letur1">&#160;</div>
					</td>
				</xsl:if>
			</tr>
		</table>
	</div>
</xsl:template>

<!-- Greiðslumáti Millifærsla -->
<xsl:template name="GreidslumatiMillifaersla">
	<div class="leftgreidsluupplysingaruppl">
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder">
			<tr>
				<td width="15%" align="left">
					<div class="letur1b">Banki:</div>
				</td>
				<td width="20%" align="left">
					<div class="letur1b">Höfuðbók:</div>
				</td>
				<td width="25%" align="left">
					<div class="letur1b">Reikningsnr.</div>
				</td>
				<td width="23%" align="left">
					<div class="letur1b">Kennitala:</div>
				</td>
			</tr>
			<tr>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cac:FinancialInstitutionBranch/cbc:ID"/>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:call-template name="hofudbok">
							<xsl:with-param name="text" select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:AccountTypeCode"/>
						</xsl:call-template>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:ID"/>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:choose>
							<xsl:when test="cac:PayeeParty">
								<xsl:value-of select="cac:PayeeParty/cac:PartyLegalEntity/cbc:CompanyID"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID"/>
							</xsl:otherwise>
						</xsl:choose>
					</div>
				</td>
			</tr>
		</table>
		<p style="line-height:0.5em"/>
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder">
			<tr>
				<td>
					<div class="letur1b">
						<xsl:call-template name="MottakandiGreidsluFyrirsogn"/>
					</div>
					<div class="letur1">
						<xsl:call-template name="MottakandiGreidslu"/>
					</div>
				</td>
				<xsl:if test="cac:PaymentMeans/cbc:PaymentID[.!='']">
					<td>
						<div class="letur1">&#160;</div>
						<div class="letur1b">Tilvísun greiðslu:</div>
						<div class="letur1">
							<xsl:value-of select="cac:PaymentMeans/cbc:PaymentID"/>
						</div>
						<div class="letur1">&#160;</div>
					</td>
				</xsl:if>
			</tr>
		</table>
	</div>
</xsl:template>

	<!-- Greiðslumáti Reikningsfært -->
<xsl:template name="GreidslumatiReikningsfaert">
	<div class="leftgreidsluupplysingaruppl">
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder" border="0">
			<tr height="70">
				<td height="100%" align="center" valign="middle">
					<div class="letur1b">Engar Greiðsluupplýsingar</div>
				</td>
			</tr>
		</table>
	</div>
</xsl:template>
	
	<!-- Greiðslumáti Staðgreitt -->	
<xsl:template name="GreidslumatiStadgreitt">
	<div class="leftgreidsluupplysingaruppl">
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder" border="0">
			<tr height="70">
				<td height="100%" align="center" valign="middle">
					<div class="letur1b">Reikningur Staðgreiddur</div>
				</td>
			</tr>
		</table>
	</div>
</xsl:template>
	
	<!-- Greiðslumáti Ávísun -->	
<xsl:template name="GreidslumatiAvisun">
	<div class="leftgreidsluupplysingaruppl">
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder" border="0">
			<tr height="70">
				<td height="100%" align="center" valign="middle">
					<div class="letur1b">Greitt með ávísun</div>
				</td>
			</tr>
		</table>
	</div>
</xsl:template>	

	<!-- Greiðslumáti Erlend Millifærsla -->
<xsl:template name="GreidslumatiErlendMillifaersla">
	<div class="leftgreidsluupplysingaruppl">
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder">
			<tr>
				<td width="40%" align="left">
					<div class="letur1b">IBAN Númer:</div>
				</td>
				<td width="20%" align="left">
					<div class="letur1b">Gjaldmiðill:</div>
				</td>
				<td width="20%" align="left">
					<div class="letur1b">BIC númer:.</div>
				</td>
			</tr>
			<tr>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:ID"/>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:CurrencyCode"/>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cac:FinancialInstitutionBranch/cac:FinancialInstitution/cbc:ID"/>
					</div>
				</td>
			</tr>
		</table>
	</div>
</xsl:template>
	
	<!-- Greiðslumáti Gíróseðill -->
<xsl:template name="GreidslumatiGirosedill">
	<div class="leftgreidsluupplysingaruppl">
		<table width="100%" cellspacing="0" cellpadding="0" class="ntablenoborder">
			<tr>
				<td width="28%" align="left">
					<div class="letur1b">Tilvísunarnr./kt</div>
				</td>
				<td width="15%" align="left">
					<div class="letur1b">Seðilnr.</div>
				</td>
				<td width="7%" align="left">
					<div class="letur1b">Fl.</div>
				</td>
				<td width="15%" align="left">
					<div class="letur1b">Banki:</div>
				</td>
				<td width="17%" align="left">
					<div class="letur1b">Höfuðbók:</div>
				</td>
				<td width="18%" align="left">
					<div class="letur1b">Reikningsnr.</div>
				</td>
			</tr>
			<tr>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cbc:PaymentID"/>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cbc:InstructionNote"/>
					</div>
				</td>
				<td align="left">
					<div class="letur1">xx</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cac:FinancialInstitutionBranch/cbc:ID"/>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:call-template name="hofudbok">
							<xsl:with-param name="text" select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:AccountTypeCode"/>
						</xsl:call-template>
					</div>
				</td>
				<td align="left">
					<div class="letur1">
						<xsl:value-of select="cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:ID"/>
					</div>
				</td>
			</tr>
			<tr height="20">
				<td colspan="6"/>
			</tr>
			<tr>
				<td width="50%" align="left" colspan="3">
					<div class="letur1b">
						<xsl:call-template name="MottakandiGreidsluFyrirsogn"/>
					</div>
				</td>
				<td width="50%" colspan="3" align="left">
					<div class="letur1">A/B Gíró - Flokkur 31</div>
				</td>
			</tr>
			<tr>
				<td align="left" colspan="3">
					<div class="letur1">
						<xsl:call-template name="MottakandiGreidslu"/>
					</div>
				</td>
				<td align="left" colspan="3">
					<div class="letur1">C Gíró - Flokkur 33</div>
				</td>
			</tr>
		</table>
	</div>
</xsl:template>

	<!-- Greiðslumáti Móttakandi greiðslu Fyrirsögn -->
<xsl:template name="MottakandiGreidsluFyrirsogn">
	<xsl:if test="cac:PayeeParty">
		Móttakandi greiðslu:
    </xsl:if>	
</xsl:template>

	<!-- Greiðslumáti Móttakandi greiðslu nafn -->
<xsl:template name="MottakandiGreidslu">
	<xsl:if test="cac:PayeeParty">
		<xsl:value-of select="cac:PayeeParty/cac:PartyName"/>
	</xsl:if>
</xsl:template>
	
<!-- Kennitala -->	
<xsl:template name="kennitala">
	<xsl:param name="text"/>
	<xsl:if test="$text[.!='']">
		<xsl:value-of select="concat(substring($text, 1, 6), '-', substring($text, 7, 4))"/>
	</xsl:if>
</xsl:template>
	
<!-- Höfuðbók -->
<xsl:template name="hofudbok">
	<xsl:param name="text"/>
	<xsl:choose>
		<xsl:when test="contains($text,':')">
			<xsl:value-of select="substring-after($text,':')"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$text"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
	

<!-- Dagsetning á íslensku formi -->
<xsl:template name="icedate">
	<xsl:param name="text"/>
		<xsl:if test="$text[.!='']">
			<xsl:value-of select="concat(substring($text, 9, 2),'.', substring($text, 6, 2),'.', substring($text, 1, 4))"/>
		</xsl:if>	
</xsl:template>
	
<!-- Dagsetning fyrir gjalddaga -->
<xsl:template name="icedateshort">
	<xsl:param name="text"/>
	<xsl:if test="$text[.!='']">
		<xsl:value-of select="concat(substring($text, 9, 2), substring($text, 6, 2), substring($text, 3, 2))"/>
	</xsl:if>
</xsl:template>
	

<!-- Númer með tvo aukastafi -->
<xsl:template name="icenumberdecdef">
	<xsl:param name="text"/>
	<xsl:choose>
		<xsl:when test="$text[.!='']">
			<xsl:value-of select='format-number($text, "###.##0,00", "IcelandicNumber")'/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select='format-number(0.00, "###.##0,00", "IcelandicNumber")'/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
	
<!-- Númer með einn aukastaf -->
<xsl:template name="icenumberdecdef2">
	<xsl:param name="text"/>
	<xsl:choose>
		<xsl:when test="$text[.!='']">
			<xsl:value-of select='format-number($text, "###.##0,0", "IcelandicNumber")'/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select='format-number(0.00, "###.##0,0", "IcelandicNumber")'/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
	

<!-- Afsláttur / Gjald á einingu -->
<xsl:template name="afslatturgjold">
	<xsl:param name="afsl" select="cac:Price/cac:AllowanceCharge/cbc:ChargeIndicator"/>
	<xsl:choose>
		<xsl:when test="$afsl[.='true']">
			Gjald
		</xsl:when>
		<xsl:when test="$afsl[.='false']">
			Afsláttur
		</xsl:when>
	</xsl:choose>
</xsl:template>
	
<!-- Afsláttur / Gjald á haus -->
<xsl:template name="afslatturgjoldlinum">
	<xsl:param name="afsl" select="cbc:ChargeIndicator"/>
	<xsl:choose>
		<xsl:when test="$afsl[.='true']">
			Gjald
		</xsl:when>
		<xsl:when test="$afsl[.='false']">
			Afsláttur
		</xsl:when>
	</xsl:choose>
</xsl:template>	

<!-- Samanlagður vsk á línum -->
<xsl:template name="upphaedmvsktaxamount">
	<xsl:variable name="amount" select="cbc:LineExtensionAmount"/>
	<xsl:variable name="tax" select="cac:TaxTotal/cbc:TaxAmount"/>
	<xsl:value-of select='format-number($amount + $tax, "###.##0,00", "IcelandicNumber")'/>
</xsl:template>	
	
<!-- Uppreiknaður vsk á línum -->
<xsl:template name="upphaedmvskreiknad">
	<xsl:param name="vskkodi" select="cac:Item/cac:ClassifiedTaxCategory/cbc:ID"/>
	<xsl:param name="amount" select="cac:InvoiceLine/cbc:LineExtensionAmount"/>
	<xsl:choose>
		<xsl:when test="$vskkodi[.='S']">
			<xsl:call-template name="vskupphaedS">
				<xsl:with-param name="upph" select="cbc:LineExtensionAmount"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:when test="$vskkodi[.='AA']">
			<xsl:call-template name="vskupphaedAA">
				<xsl:with-param name="upph" select="cbc:LineExtensionAmount"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select='format-number(cbc:LineExtensionAmount, "###.##0,00", "IcelandicNumber")'/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!-- Vsk tegund S -->
<xsl:template name="vskupphaedS">
	<xsl:param name="upph"/>
	<xsl:for-each select="../cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory">
		<xsl:choose>
			<xsl:when test="cbc:ID[.='S']">
				<xsl:variable name="vskprosent" select="cbc:Percent"/>
				<xsl:value-of select='format-number($upph * (1+($vskprosent)*0.01), "###.##0,00", "IcelandicNumber")'/>
			</xsl:when>
		</xsl:choose>
	</xsl:for-each>
</xsl:template>
	
<!-- Vsk tegund AA -->
<xsl:template name="vskupphaedAA">
	<xsl:param name="upph"/>
	<xsl:for-each select="../cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory">
		<xsl:choose>
			<xsl:when test="cbc:ID[.='AA']">
				<xsl:variable name="vskprosent" select="cbc:Percent"/>
				<xsl:value-of select='format-number($upph * (1+($vskprosent)*0.01), "###.##0,00", "IcelandicNumber")'/>
			</xsl:when>
		</xsl:choose>
	</xsl:for-each>
</xsl:template>

<!-- Einingar -->
<xsl:template name="Einingar">
	<xsl:param name="text"/>
	<xsl:choose>
		<xsl:when test="$text[.='C62']">stk</xsl:when>
		<xsl:when test="$text[.='KGS']">kg</xsl:when>
		<xsl:when test="$text[.='KGM']">kg</xsl:when>
		<xsl:when test="$text[.='MTR']">m</xsl:when>
		<xsl:when test="$text[.='LTR']">l</xsl:when>
		<xsl:when test="$text[.='MTK']">m²</xsl:when>
		<xsl:when test="$text[.='MTQ']">m³</xsl:when>
		<xsl:when test="$text[.='KMT']">km</xsl:when>
		<xsl:when test="$text[.='TNE']">t</xsl:when>
		<xsl:when test="$text[.='KWH']">kWh</xsl:when>
		<xsl:when test="$text[.='DAY']">d</xsl:when>
		<xsl:when test="$text[.='HUR']">klst</xsl:when>
		<xsl:when test="$text[.='MIN']">mín</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$text"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!-- Samtala afslátt á forsíðu -->
<xsl:template name="samtalaafslatta">
	<xsl:variable name="tmpTotaltrue">
           <xsl:value-of select="sum(cac:AllowanceCharge/cbc:Amount[../cbc:ChargeIndicator='true'])"/>
	</xsl:variable>
	<xsl:variable name="tmpTotalfalse">
		<xsl:value-of select="sum(cac:AllowanceCharge/cbc:Amount[../cbc:ChargeIndicator='false'])"/>
	</xsl:variable>
	<xsl:value-of select='format-number($tmpTotaltrue - $tmpTotalfalse, "###.##0,00", "IcelandicNumber")'/>
</xsl:template>

</xsl:stylesheet>
