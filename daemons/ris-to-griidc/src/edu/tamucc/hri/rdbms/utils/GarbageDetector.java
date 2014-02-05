package edu.tamucc.hri.rdbms.utils;

import java.io.BufferedReader;
import java.io.IOException;

import edu.tamucc.hri.griidc.support.MiscUtils;

/**
 * This is an object that will read the contents of a string a detect the
 * presence of unwanted characters, strings and patterns. Specifically html
 * coding in the RIS relational database
 * 
 * @author jvh
 * 
 */
public class GarbageDetector {

	public static boolean Debug = false;

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}

	public GarbageDetector() {
		super();
	}

	public static String[] htmlTokens = { "<a>", "<a ", "<a", "</a", "<p>", "</p>",
			"<table", "</table", "<tbody>", "</tbody>", "<tr>", "<td>", "<h1>",
			"<h2>", "<h3>", "<ul>", "<li>", "</li>", "<div>", "</div>",
			"&nbsp;", "&nbsp", "<a href=\"", "<a href="

	};

	/**
	 * look for the htmlTokens in the line. If one is found return the token
	 * that was found in the string. If none are found return null
	 * 
	 * @param line
	 * @return
	 */
	public boolean hasHtmlCode(final String line) {
		return this.contains(line, htmlTokens);
	}

	public String filterHtmlCode(final String line) {
		String filteredLine = new String(line);
		for (String t : htmlTokens) {
			trips = 0;
			filteredLine = this.removeFirstBadStringFirstTime(filteredLine, t);
			if(Debug && (this.getTrips() > 0)) System.out.println("\t " + this.getTrips() + " trip through line for token: " + t);
		}
		return filteredLine;
	}

	private int trips = 0;
	
	public int getTrips() {
		return trips;
	}
	/**
	 * remove the first occurrence of the target from the line, do this until
	 * all occurrences are removed (recursive)
	 * 
	 * @param line
	 * @param target
	 * @return
	 */

	private String removeFirstBadString(final String line, final String target) {
		String s = line;
		int ndx = s.indexOf(target);
		if (ndx >= 0) { // found it - ndx points to start of target occurrence
			s = line.substring(0, ndx) + line.substring(ndx + target.length());
			trips++;
			this.removeFirstBadString(s, target);
		} 
		return s;
	}
	
	private String removeFirstBadStringFirstTime(final String line, final String target) {
	  trips = 0;
	  return removeFirstBadString(line,target);
	}

	/**
	 * return the first target found if one of the targets is contained anywhere
	 * in the line.
	 * 
	 * @param line
	 * @param target
	 * @return
	 */
	public boolean contains(final String line, final String[] target) {

		for (String t : target) {
			if (line.contains(t))
				return true;
		}
		return false;
	}

	public static final String defaultTestFileName = "oneLineHtmlGarbage.txt";

	public static void main(String[] args) {

		GarbageDetector.setDebug(true);
		String inFileName = GarbageDetector.defaultTestFileName;
		if (args.length > 0 && args[0] != null)
			inFileName = args[0];
		GarbageDetector gbd = new GarbageDetector();
		try {
			BufferedReader reader = MiscUtils.openInputFile(inFileName);
			System.out.println("GarbageDetector.main() - input file is "
					+ MiscUtils.getAbsoluteFileName(inFileName));

			for (String line = reader.readLine(); line != null; line = reader
					.readLine()) {
				 ;
				if (gbd.hasHtmlCode(line))
					System.out.println("\nfound trash in line: "
							+ line);
				else
					System.out.println("NO GARBAGE in " + line);
				String cleanLine = gbd.filterHtmlCode(line);
				System.out.println("clean line: " + cleanLine);
			}
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

}
