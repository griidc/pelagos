package edu.tamucc.hri.griidc.pubs;

import java.io.IOException;
import java.nio.charset.Charset;

import edu.tamucc.hri.griidc.exception.FileNotXmlException;
import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.PubsConstants;

/**
 * This class is used to replace some characters and Strings in the file that
 * are illegal in XML
 * 
 * @author jvh
 * 
 */
public class XmlPreprocessor {

	public static boolean DeBug = false;

	private String fileBuffer = null;
	private String fileName = null;

	public XmlPreprocessor(String fileName) {
		this.fileName = fileName;
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	/**
	 * this function reads the file into a buffer (String) applies all the
	 * filters and then rewrites the clean file back to it's original name. the
	 * filename is presumed to be the complete file system path name
	 * 
	 * @param fileName
	 * @return
	 * @throws IOException
	 */
	private boolean filter() throws IOException {
		String localBuffer = this.getFileBuffer();
		localBuffer = badCharacterFilter(localBuffer);
		localBuffer = ampersandFilter(localBuffer);
		return true;
	}

	/**
	 * lazy instantiation of the file buffer. This insures that we only read the
	 * file one time
	 * 
	 * @return
	 * @throws IOException
	 */
	private String getFileBuffer() throws IOException {
		if (this.fileBuffer == null) {
			this.fileBuffer = MiscUtils.readFileToBuffer(MiscUtils
					.prependUserDirDataPrefixToFileName(fileName));
		}
		return this.fileBuffer;
	}

	/**
	 * write the file buffer back to the xml file with the same name
	 * @throws IOException 
	 */
	private void writeBufferToFile() throws IOException {
	      MiscUtils.writeStringToFile(this.fileName, this.fileBuffer);
}
	/**
	 * This function reads a file into a buffer and examines the first line to
	 * determine if the file is in fact an XML file. RefBase returns an html
	 * file if can not satisfy the request.
	 * 
	 * @param fileName
	 * @return
	 */
	private boolean isXmlFile() throws IOException, FileNotXmlException {
		String localBuffer = this.getFileBuffer();
		if (localBuffer.startsWith(PubsConstants.FirstLineOfXmlFile)) {
			return true;
		}
		this.debugMessage("XmlPreprocessor.isXmlFile() -  NO NOT XML");
		throw new FileNotXmlException();
	}

	/**
	 * this is the external interface to this object. It 
	 * reads the file, checks to see if the file is xml,
	 * if xml it processes it (makes a few fixes) and
	 * writes it back to the same name.
	 * @throws IOException
	 * @throws FileNotXmlException
	 */
	public void processFile() throws IOException, FileNotXmlException {
		this.getFileBuffer();
		if(this.isXmlFile()) { 
			this.filter();
		    this.writeBufferToFile();
		}
	}
	private static char[] badChars = { 0x13, 0x2, 0xe };

	private static String[] badFiles = { "refBase2014-06-04:10-27-41.xml",
			"refBase2014-06-04:10-27-54.xml", "refBase2014-06-04:10-29-05.xml",
			"refBase2014-06-04:10-29-18.xml", "refBase2014-06-04:10-29-24.xml" };

	private String badCharacterFilter(final String src) {
		StringBuffer outputBuffer = new StringBuffer();
		int bCount = 0;
		char[] srcChars = src.toCharArray();
		for (char c : srcChars) {
			bCount++;
			// if (isDeBug())
			// System.out.print(c);
			if (isGoodCharacter(c)) {
				outputBuffer.append(c);
			} else {
				if (isDeBug())
					System.out
							.println( "XmlPreprocessor.badCharacterFilter() at position "
									+ bCount + " bad char: 0x"
									+ Integer.toHexString(c));
			}
		}
		return outputBuffer.toString();
	}

	private String ampersandFilter(final String src) {
		String res = ampersandFilterWorker(src);
		return res;
	}

	public static String badAmpTarget = "&amp";
	public static String goodAmpTarget = "&amp;";

	private static String[] testStrings = {
			"aaabbbcccdddeeefffggghhhiiijjjkkklllmmmnnn",
			"aaabbbccc &amp dddeeefffggg &amp; hhhiiijjjkkklllmmmnnn",
			"aaa &amp bbb &amp ccc &amp ddd &amp eee &amp fff &amp ggg &amp hhh &amp iii &amp jjj &amp kkk &amp lll &amp mmm &amp nnn",
			"aaabbbccc &amp; dddeeefffggg &amp hhhiiijjjkkk &amp; lllmmm&ampnnnooo &amp pppqqq",
			"<topic>microbial sulfate reduction</topic>  <topic>C1 &amp </topic> </subject>" };

	private String ampersandFilterWorker(final String src) {
		return ampersandFilterWorker(src, 0);
	}

	/**
	 * there is an offending string that appears in the xml "&amp" which if not
	 * followed by a ; is illegal
	 * 
	 * @param src
	 * @param startNdx
	 * @return
	 */

	private String ampersandFilterWorker(final String src, int startNdx) {

		int sNdx = src.indexOf(badAmpTarget, startNdx);
		if (sNdx == -1) { // it does not occur
			return src;
		}
		int lNdx = src.indexOf(goodAmpTarget, startNdx);
		if (sNdx == lNdx) { // no problem the shortAmpTarget is the first part
							// of the longAmpTarget
			return ampersandFilterWorker(src, lNdx + goodAmpTarget.length());
		}
		// so..
		// sNdx != -1 indicating that we found the short target
		// lNdx, if not -1 is further in the string
		// remove the offending shortAmpTarget from the string and pass it on
		// starting at the front
		String front = src.substring(0, sNdx);
		String back = src.substring(sNdx + badAmpTarget.length());
		StringBuffer sb = new StringBuffer();
		sb.append(front);
		sb.append(goodAmpTarget);
		sb.append(back);

		return ampersandFilterWorker(sb.toString(), 0);
	}

	private boolean isGoodCharacter(char c) {
		for (char badC : badChars) {
			if (c == badC) {
				return false; // don't copy
			}
		}
		return true;
	}

	private static void debugMessage(String msg) {
		if (isDeBug())
			System.out.println(msg);
	}

	public static void main(String[] args) {
		// XmlPreprocessor.setDeBug(true);
		debugMessage("XmlPreprocessor.main()");
		XmlPreprocessor xmlp = null;

		for (String fileName : badFiles) {
			try {
				xmlp = new XmlPreprocessor(fileName);
				xmlp.filter();
			} catch (IOException e) { // TODO Auto-generated catch block
				e.printStackTrace();
			}
		}

		for (String s : testStrings) {
			System.out.println("\nAmp filter src: " + s);
			String s2 = xmlp.ampersandFilterWorker(s);
			System.out.println("Amp filter out: " + s2);
		}
		debugMessage("XmlPreprocessor.main() END");
	}

}
