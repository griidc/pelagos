package edu.tamucc.hri.griidc.xml;

import java.io.ByteArrayOutputStream;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.PrintStream;
import java.nio.channels.FileChannel;

import edu.tamucc.hri.griidc.utils.MiscUtils;

public class RefBaseXmlErrorProcessor {

	private String fileName = null;
	private int serialId = -1;
	private String jaxbExceptionMessage = null;
	private String urlRequest = null;
	public static final String XmlExtension = ".xml";
	public static final String TxtExtension = ".txt";
	public static RefBaseXmlErrorProcessor instance;
	public static boolean DeBug = false;

	/**
	 * @param fileName
	 * @param serialId
	 * @param jaxbExceptionMessage
	 */
	private RefBaseXmlErrorProcessor(String fileName, int serialId, 
			String jaxbExceptionMessage, String urlRequest) {
		super();
		this.fileName = fileName;
		this.serialId = serialId;
		this.urlRequest = urlRequest;
		this.jaxbExceptionMessage = jaxbExceptionMessage;
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	public String getFileName() {
		return fileName;
	}

	public int getSerialId() {
		return serialId;
	}

	public String getJaxbExceptionMessage() {
		return jaxbExceptionMessage;
	}

	public String getUrlRequest() {
		return this.urlRequest;
	}
	public static RefBaseXmlErrorProcessor createRefBaseXmlError(
			String xmlFileName, int serialId, String jaxbExceptionMessage, String urlRequest)
			throws IOException {
		String extraInformation = "SerID_" + serialId + "_" + MiscUtils.getDateAndTime();
		String xmlOrigFilePath = MiscUtils
				.prependUserDirDataPrefixToFileName(xmlFileName);
		String xmlCopyFilePath = MiscUtils
				.prependUserDirDataPrefixToFileName(insertExtraInformationFileName(
						xmlFileName, extraInformation, XmlExtension));
		String textFileName = insertExtraInformationFileName(xmlFileName,
				extraInformation, TxtExtension);
		String txtFilePath = MiscUtils
				.prependUserDirDataPrefixToFileName(textFileName);

		copyFile(xmlOrigFilePath, xmlCopyFilePath);
		instance = new RefBaseXmlErrorProcessor(xmlCopyFilePath, serialId,
				jaxbExceptionMessage,urlRequest);
		MiscUtils.writeStringToFile(textFileName, instance.toFormatedString());
		if (RefBaseXmlErrorProcessor.isDeBug()) {
			System.out.println("xmlOrigFilePath: " + xmlOrigFilePath);
			System.out.println("xmlCopyFilePath: " + xmlCopyFilePath);
			System.out.println("txtFilePath: " + txtFilePath);
			System.out.println("RefBaseXmlError: " + instance.toString());
		}
		return instance;
	}

	private static void copyFile(String sourceFileName, String outputFileName)
			throws IOException {
		copyFilesUsingBuffers(sourceFileName, outputFileName);
		// copyFileUsingFileChannels(sourceFileName, outputFileName);
	}

	private static void copyFileUsingFileChannels(String sourceFileName,
			String outputFileName) throws IOException {
		FileChannel inputChannel = null;
		FileChannel outputChannel = null;
		try {
			inputChannel = new FileInputStream(sourceFileName).getChannel();
			outputChannel = new FileOutputStream(outputFileName).getChannel();
			outputChannel.transferFrom(inputChannel, 0, inputChannel.size());
		} finally {
			inputChannel.close();
			outputChannel.close();
		}
	}

	private static void copyFilesUsingBuffers(String sourceFileName,
			String outputFileName) throws IOException {
	
		if(RefBaseXmlErrorProcessor.isDeBug()) {
			System.out.println("RefBaseXmlErrorProcessor.copyFilesUsingBuffers()");
			System.out.println("RefBaseXmlErrorProcessor.copyFilesUsingBuffers() source file: " + sourceFileName);
			System.out.println("RefBaseXmlErrorProcessor.copyFilesUsingBuffers() desti  file: " + outputFileName);
		}
		String buffer = MiscUtils.readFileToBuffer(sourceFileName);
		MiscUtils.writeStringToFilePathFile(outputFileName, buffer);
	}

	@Override
	public String toString() {
		return "RefBaseXmlError [fileName=" + fileName + ", serialId="
				+ serialId + ", jaxbExceptionMessage=" + jaxbExceptionMessage
				+ "]";
	}

	public String toFormatedString() {
		String format = "%14s: %-50s%n";
		String format2 = "%-40s%n";
		StringBuffer sb = new StringBuffer();
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);
		ps.printf(format2, "RefBase XML File Output Error");
		ps.printf(format, "serial Id", String.valueOf(this.getSerialId()));
		ps.printf(format, "XML Error", this.getJaxbExceptionMessage());
		ps.printf(format, "XML File", this.getFileName());
		ps.printf(format, "refbase url",this.getUrlRequest());
		return outStream.toString();
	}

	/**
	 * take an xml file name (presumed to have extension .xml) insert the
	 * extraInformation before the extension and return the new filename
	 * 
	 * @param fileName
	 * @return
	 */
	public static String insertExtraInformationFileName(final String fileName,
			final String extraInformation, String extension) {
		return removeXmlExtension(fileName) + extraInformation + extension;
	}

	public static String removeXmlExtension(final String fileName) {
		int ndx = fileName.indexOf(XmlExtension);
		return fileName.substring(0, ndx);
	}

	public static void main(String[] args) {
		RefBaseXmlErrorProcessor.setDeBug(true);
		String fileName = "refBase.xml";
		int sId = 1010;
		String msg = "Something bad happened";
		RefBaseXmlErrorProcessor.setDeBug(true);
		RefBaseXmlErrorProcessor rbxe = null;
		try {
			rbxe = RefBaseXmlErrorProcessor.createRefBaseXmlError(fileName,
					sId, msg, "URL goes here");

			String xmlFileIn = "/Users/jvh/griidcProjects/eclipseJavaWorkSpace/PubsToGriidc/refBase.xml";
			String xmlFileOut = "/Users/jvh/griidcProjects/eclipseJavaWorkSpace/PubsToGriidc/refBaseOut.xml";
			RefBaseXmlErrorProcessor.copyFile(xmlFileIn, xmlFileOut);
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

}
