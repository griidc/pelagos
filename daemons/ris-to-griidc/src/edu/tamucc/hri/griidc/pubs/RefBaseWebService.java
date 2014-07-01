package edu.tamucc.hri.griidc.pubs;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.URL;

import edu.tamucc.hri.griidc.utils.PubsConstants;
import edu.tamucc.hri.griidc.utils.MiscUtils;;

public class RefBaseWebService {

	public static String SPACE_CHAR = "%20";
	public static String COMMA_CHAR = "%2C";

	public static String outputFileName = PubsConstants.RefBaseXmlFileName;
	public static String XML = ".xml";
	public static String HTML = ".html";
	public static String Curl = "/usr/bin/curl -0 ";

	public static String authorTag = "dc.creator";
	public static String pubSerialNumTag = "rec.identifier";
	public static String EqualSign = "=";

	public static String RefBaseUrl = "http://research.gulfresearchinitiative.org/refBase-pub/";

	public static String SruExplainRequest = "operation=explain";

	private String requestUrl = null;
	public static String SruSerialNumberRequest = "sru.php?version=1.1&query=serial=";
	public static String SruAuthorRequest = "sru.php?version=1.1&query=author=";
	public static String SruSuffix = "&recordPacking=xml&stylesheet=";

	public static String SqlSelectTerms = "search.php?sqlQuery=SELECT author, title, publication, abstract, created_by, serial FROM refs ";
	public static String SqlSerialWhereClause = "WHERE serial = ";
	public static String SqlAuthorWhereClause = "WHERE author = ";
	public static String[] RefBaseFields = { "author", "title", "type", "year",
			"publication", "abbrev_journal", "volume", "issue", "pages",
			"keywords", "abstract", "address", "corporate_author", "thesis",
			"publisher", "place", "editor", "language", "summary_language",
			"orig_title", "series_editor", "series_title",
			"abbrev_series_title", "series_volume", "series_issue", "edition",
			"issn", "" + "isbn", "medium", "area", "expedition", "conference",
			"notes", "approved", "contribution_id", "online_publication",
			"online_citation", "created_date", "created_time", "created_by",
			"modified_date", "modified_time", "modified_by", "call_number",
			"serial" };

	public static boolean DeBug = false;

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	public RefBaseWebService() {
		// TODO Auto-generated constructor stub
	}

	public static String getOutputFileName() {
		return outputFileName;
	}

	/**
	 * read the xml stream from the web site and write it to a file Return the
	 * name of the xml file
	 * 
	 * @return
	 * @throws IOException
	 */
	public String submitRefBaseQuery(String urlQuery) throws IOException {
		if(RefBaseWebService.isDeBug()) System.out.println("RefBaseWebService.submitRefBaseQuery() " + urlQuery);
		URL url = new URL(urlQuery);
		InputStream in = url.openStream();
		OutputStream os = new FileOutputStream(new File(outputFileName));
		byte buffer[] = new byte[1024];
		int nBytesRead;
		while ((nBytesRead = in.read(buffer)) != -1) {
			os.write(buffer, 0, nBytesRead);
		}
		os.flush();
		os.close();
		in.close();
		if(RefBaseWebService.isDeBug()) System.out.println("RefBaseWebService.submitRefBaseQuery() output file: " + outputFileName);
		return outputFileName;
	}

	public String getRefBaseXmlResponse(String authorLastName)
			throws IOException {
		this.requestUrl = makeAuthorRequestUrl(authorLastName);
		return submitRefBaseQuery(requestUrl);
	}

	public String getRefBaseXmlResponse(int risRecordSerialNumber)
			throws IOException {
		this.requestUrl = makePubSerialRequestUrl(risRecordSerialNumber);
		return submitRefBaseQuery(this.requestUrl);
	}

	public String makeAuthorRequestUrl(String authorName) {
		return RefBaseUrl + SruAuthorRequest + authorName + SruSuffix;
	}

	public String makePubSerialRequestUrl(int pubSerialNum) {
		return RefBaseUrl + SruSerialNumberRequest + pubSerialNum + SruSuffix;
	}
	
	public String getRequestUrl() {
		return this.requestUrl;
	}

	public static String exampleNoWorking = "search.php?sqlQuery="
			+ "SELECT author, title, type, year, publication, abbrev_journal, volume, issue, pages, keywords, abstract, address, corporate_author, "
			+ "thesis, publisher, place, editor, language, summary_language, orig_title, series_editor, series_title, abbrev_series_title, series_volume, "
			+ "series_issue, edition, issn, isbn, medium, area, expedition, conference, notes, approved, contribution_id, online_publication, online_citation, "
			+ "created_date, created_time, created_by, modified_date, modified_time, modified_by, call_number, serial FROM refs "
			+ "ORDER BY serial&formType=sqlSearch&showLinks=1&exportStylesheet=srwdc2html.xsl&showRows=500&exportType=xml&submit=Export&exportFormat=SRW_DC XML";

	public static String serialIdexampleUrl = "http://research.gulfresearchinitiative.org/refBase-pub/"
			+ "search.php?sqlQuery=SELECT author, title, type, year, publication, abbrev_journal, volume, "
			+ "issue, pages, keywords, abstract, address, corporate_author, thesis, publisher, place, editor, "
			+ "language, summary_language, orig_title, series_editor, series_title, abbrev_series_title, series_volume, "
			+ "series_issue, edition, issn, isbn, medium, area, expedition, conference, notes, approved, "
			+ "contribution_id, online_publication, online_citation, created_date, created_time, created_by, "
			+ "modified_date, modified_time, modified_by, call_number, serial FROM refs WHERE "
			+ "(serial RLIKE \"(^|[[:space:][:punct:]])776([[:space:][:punct:]]|%24)\") ORDER BY "
			+ "serial&formType=sqlSearch&showLinks=1&exportStylesheet=srwmods2html.xsl&"
			+ "showRows=500&exportType=xml&submit=Export&exportFormat=SRW_MODS XML";

	public static String allSerialIdExampleUrl = "http://research.gulfresearchinitiative.org/refBase-pub/"
			+ "search.php?sqlQuery=SELECT author, title, type, year, publication, abbrev_journal, volume, issue, pages, "
			+ "keywords, abstract, address, corporate_author, thesis, publisher, place, editor, language, summary_language, "
			+ "orig_title, series_editor, series_title, abbrev_series_title, series_volume, series_issue, edition, issn, "
			+ "isbn, medium, area, expedition, conference, notes, approved, contribution_id, online_publication, online_citation, "
			+ "created_date, created_time, created_by, modified_date, modified_time, modified_by, call_number, serial FROM refs "
			+ "WHERE (serial >= 13) ORDER BY serial&formType=sqlSearch&showLinks=1&exportStylesheet=srwmods2html.xsl&showRows=500&exportType=xml&submit=Export&exportFormat=SRW_MODS XML";

	public static void main(String[] args) {
		long start = System.currentTimeMillis();
		RefBaseWebService webService = new RefBaseWebService();
		RefBaseWebService.setDeBug(true);
		System.out.println("-- Start RefBaseWebService Main --");
		int[] publicationSerialNumbers = PubsConstants.GoodPublicationSerialNumbers;
		for (int serialNum : publicationSerialNumbers) {
			try {
				String filename = webService.getRefBaseXmlResponse(serialNum);
				System.out.println("RefBaseWebService Main for serialNumer: "
						+ serialNum + " file produced is " + filename);
			} catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}

		System.out.println("END of RefBaseWebService Main output is "
				+ webService.getOutputFileName());

		System.out
				.println("Elapsed time:"
						+ MiscUtils.formatElapsedTime(start,
								System.currentTimeMillis()));
	}
}
