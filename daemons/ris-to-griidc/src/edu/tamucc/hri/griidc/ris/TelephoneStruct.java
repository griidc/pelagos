package edu.tamucc.hri.griidc.ris;

import edu.tamucc.hri.griidc.exception.TelephoneNumberException;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class TelephoneStruct {

	private int key = -1;
	private int countryNumber = -1;
	private String telephoneNumber = null;
	private String extension = null;

	public TelephoneStruct() {
	}

	public TelephoneStruct(int countryNumber, String telephoneNumber) {
		this(-1, countryNumber, telephoneNumber);
	}

	/**
	 * @param key
	 * @param countryNumber
	 * @param telephoneNumber
	 */
	public TelephoneStruct(int key, int countryNumber, String telephoneNumber) {
		super();
		this.key = key;
		this.countryNumber = countryNumber;
		this.telephoneNumber = telephoneNumber.trim();
		this.separateExtension();
		this.compressPhoneNumber();
	}

	public int getKey() {
		return key;
	}

	public void setKey(int key) {
		this.key = key;
	}

	public int getCountryNumber() {
		return countryNumber;
	}

	public void setCountryNumber(int countryNumber) {
		this.countryNumber = countryNumber;
	}

	public String getTelephoneNumber() {
		return telephoneNumber;
	}

	public void setTelephoneNumber(String telephoneNumber) {
		this.telephoneNumber = telephoneNumber;
	}
	

	public String getExtension() {
		return extension;
	}

	public static TelephoneStruct createTelephoneStruct(int countryNumber,
			String telephoneNumber) {
		return new TelephoneStruct(countryNumber, telephoneNumber);
	}
	/**
	 * for phone number that has extension on the end The ' x' is used in RIS
	 * records
	 */
	private static String[] ExtensionPatterns = { " x", " ex", " EX", " X" };

	public void separateExtension() {
		String[] px = MiscUtils.separateTelephoneNumberExtension(
				this.telephoneNumber.trim(), this.ExtensionPatterns);
		this.telephoneNumber = px[0];
		this.extension = px[1];
	}
	/**
	 * remove formating characters from the phone number
	 */
	public void compressPhoneNumber() {
		char[] ca = this.telephoneNumber.toCharArray();
		StringBuffer sb = new StringBuffer();
		for(char c : ca) {
			if(Character.isDigit(c)) {
				sb.append(c);
			}
		}
		this.telephoneNumber = sb.toString();
	}

	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime * result + countryNumber;
		result = prime * result
				+ ((extension == null) ? 0 : extension.hashCode());
		result = prime * result
				+ ((telephoneNumber == null) ? 0 : telephoneNumber.hashCode());
		return result;
	}

	@Override
	public boolean equals(Object obj) {
		if (this == obj)
			return true;
		if (obj == null)
			return false;
		if (getClass() != obj.getClass())
			return false;
		TelephoneStruct other = (TelephoneStruct) obj;
		if (countryNumber != other.countryNumber)
			return false;
		if (extension == null) {
			if (other.extension != null)
				return false;
		} else if (!extension.equals(other.extension))
			return false;
		if (telephoneNumber == null) {
			if (other.telephoneNumber != null)
				return false;
		} else if (!telephoneNumber.equals(other.telephoneNumber))
			return false;
		return true;
	}

	@Override
	public String toString() {
		return "TelephoneStruct [key=" + key + ", countryNumber="
				+ countryNumber + ", telephoneNumber=" + telephoneNumber
				+ ", extension=" + extension + "]";
	}
	public String toShortString() {
		return "[countryNumber=" + countryNumber + 
				", telephoneNumber=" + telephoneNumber + 
				", extension=" + extension + "]";
	}
	public static void main(String[] args) {
		
		int targetCountry = 42;
		String[] targetTelNum = { "(505) 690-5673 x123", "123-456-7890",
				"123-456-7890 ex123", "012-345-6789   EX 4567",
				"(505) 690-5673", "505-690-5673", "5056905673"
		};
		
		String paren1 = "(505) 690-5673";
		String noParen = "505-690-5673";
		String compressed = "5056905673";

		TelephoneStruct[] telArray = new TelephoneStruct[targetTelNum.length];
		TelephoneSynchronizer.setDebug(true);
		int ndx = 0;
		try {
			for (String telNum : targetTelNum) {
				TelephoneStruct tst = TelephoneStruct.createTelephoneStruct(targetCountry,
						telNum);
				telArray[ndx++] = tst;
				MiscUtils.isValidPhoneNumber(tst.getTelephoneNumber());
				System.out.println("Number: " + telNum);
				System.out.println("\t" + tst);
			}
			
			for(int i = 0; i< telArray.length;i++) {
				for(int j = 0; j <telArray.length;j++) {
					if(i != j) {
						TelephoneStruct ts1 = telArray[i];
						TelephoneStruct ts2 = telArray[j];
						if(ts1.equals(ts2)) {
							System.out.println(ts1.toShortString() + " Equals " + ts2.toShortString());
						}
					}
				}
			}
		} catch (TelephoneNumberException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
}
