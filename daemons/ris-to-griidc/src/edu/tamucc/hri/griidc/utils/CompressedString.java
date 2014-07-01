package edu.tamucc.hri.griidc.utils;


public class CompressedString  implements Comparable<CompressedString>  {

	private String compressedString  = null;
	private String originalString = null;
	
	/**
	 * 
	 */
	public CompressedString() {
		super();
	}
	public CompressedString(String s) {
		this.originalString = s;
		this.compressedString = compressString(s);
	}
	public String getCompressedString() {
		return this.compressedString;
	}
	public void set(String s) {
		this.originalString = s;
		this.compressedString = compressString(s);
	}
	
	public String getOriginalString() {
		return this.originalString;
	}
	public String toString() {
		return this.getCompressedString();
	}
	public boolean equals(CompressedString sqs) {
		return this.getCompressedString().equals(sqs.getCompressedString());
	}
	
	@Override
	public int compareTo(CompressedString other) {
		return this.getCompressedString().compareTo(other.getCompressedString());
	}
	
	public static String compressString(String s) {
		return MiscUtils.squeeze(s);
	}
}
